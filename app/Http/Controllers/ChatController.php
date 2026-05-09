<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function inbox()
    {
        $threads = ChatThread::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
            ->with(['users:id,username,avatar_seed,avatar_url,verification_tier'])
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get();

        return view('chat.inbox', compact('threads'));
    }

    public function open(User $user)
    {
        if ($user->id === Auth::id()) abort(422);
        $thread = ChatThread::between(Auth::id(), $user->id);
        return redirect()->route('chat.show', $thread);
    }

    public function show(ChatThread $thread)
    {
        $this->authorize($thread);

        $thread->load(['users:id,username,avatar_seed,avatar_url,verification_tier']);
        $messages = $thread->messages()
            ->with('user:id,username,avatar_url,avatar_seed')
            ->oldest()
            ->limit(200)
            ->get();

        // Mark as read
        DB::table('chat_thread_users')
            ->where('thread_id', $thread->id)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        $other = $thread->users->firstWhere('id', '!=', Auth::id());

        return view('chat.show', compact('thread', 'messages', 'other'));
    }

    public function send(ChatThread $thread, Request $request)
    {
        $this->authorize($thread);

        $key = 'dm:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            throw ValidationException::withMessages(['body' => 'بتبعت رسايل كتير قوي. استنى دقيقة.']);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $msg = ChatMessage::create([
            'thread_id'  => $thread->id,
            'user_id'    => Auth::id(),
            'body'       => $data['body'],
            'created_at' => now(),
        ]);

        $thread->update([
            'last_message_user_id' => Auth::id(),
            'last_message_preview' => mb_substr($data['body'], 0, 200),
            'last_message_at'      => now(),
        ]);

        // Notify the other user
        $other = $thread->users()->where('users.id', '!=', Auth::id())->first();
        if ($other) {
            \App\Services\PushService::sendToUser($other->id, [
                'title' => '💬 '.Auth::user()->username,
                'body'  => mb_substr($data['body'], 0, 90),
                'url'   => route('chat.show', $thread),
                'tag'   => 'chat-'.$thread->id,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => [
                    'id'      => $msg->id,
                    'user_id' => $msg->user_id,
                    'body'    => $msg->body,
                    'mine'    => true,
                    'time'    => 'الآن',
                ],
            ]);
        }
        return redirect()->route('chat.show', $thread);
    }

    /** Report the other user in a chat thread. Stores a Report row only — no admin push. */
    public function report(ChatThread $thread, Request $request)
    {
        $this->authorize($thread);

        $data = $request->validate([
            'reason'  => ['required', 'in:spam,abuse,nsfw,fake,other'],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        $other = $thread->users()->where('users.id', '!=', Auth::id())->first();
        if (! $other) abort(422);

        \App\Models\Report::firstOrCreate(
            [
                'reporter_id' => Auth::id(),
                'target_type' => 'user',
                'target_id'   => $other->id,
            ],
            [
                'reason'  => $data['reason'],
                'details' => $data['details'] ?? 'بلاغ من شات (thread #'.$thread->id.')',
                'status'  => 'open',
            ]
        );

        return back()->with('flash', 'تم استلام البلاغ، شكراً.');
    }

    /** Poll for new messages (called every few seconds by JS). */
    public function poll(ChatThread $thread, Request $request)
    {
        $this->authorize($thread);
        $since = (int) $request->query('since', 0);

        $messages = $thread->messages()
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn ($m) => [
                'id'      => $m->id,
                'user_id' => $m->user_id,
                'body'    => $m->body,
                'mine'    => $m->user_id === Auth::id(),
                'time'    => $m->created_at?->diffForHumans() ?? 'الآن',
            ]);

        // Bump my last_read_at if I received new messages
        if ($messages->where('mine', false)->isNotEmpty()) {
            DB::table('chat_thread_users')
                ->where('thread_id', $thread->id)
                ->where('user_id', Auth::id())
                ->update(['last_read_at' => now()]);
        }

        return response()->json(['messages' => $messages]);
    }

    private function authorize(ChatThread $thread): void
    {
        $isMember = $thread->users()->where('users.id', Auth::id())->exists();
        if (! $isMember) abort(403);
    }
}
