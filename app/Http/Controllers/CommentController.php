<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Report;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function like(Comment $comment, Request $request)
    {
        if ($comment->status !== 'active') abort(404);

        $userId = Auth::id();
        $liked  = false;

        DB::transaction(function () use ($comment, $userId, &$liked) {
            $exists = DB::table('comment_likes')
                ->where('user_id', $userId)
                ->where('comment_id', $comment->id)
                ->exists();

            if ($exists) {
                DB::table('comment_likes')
                    ->where('user_id', $userId)
                    ->where('comment_id', $comment->id)
                    ->delete();
                $comment->decrement('upvotes');
                $liked = false;
            } else {
                DB::table('comment_likes')->insert([
                    'user_id'    => $userId,
                    'comment_id' => $comment->id,
                    'created_at' => now(),
                ]);
                $comment->increment('upvotes');
                $liked = true;

                // Push to comment owner (not self, only on new like)
                if ($comment->user_id !== $userId) {
                    $liker = Auth::user();
                    \App\Services\PushService::sendToUser($comment->user_id, [
                        'title' => '👍 '.$liker->username.' عجبه كومنتك',
                        'body'  => \Illuminate\Support\Str::limit($comment->body, 80),
                        'url'   => route('posts.show', $comment->post_id).'#comments',
                        'tag'   => 'comment-like-'.$comment->id,
                    ]);
                }
            }
        });

        $comment->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'liked'    => $liked,
                'upvotes'  => $comment->upvotes,
            ]);
        }
        return back();
    }

    public function reply(Comment $comment, Request $request)
    {
        if ($comment->status !== 'active') abort(404);

        $key = 'comment-reply:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'body' => 'استنى دقيقة قبل ما تكتب رد تاني.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'body'         => ['required', 'string', 'min:1', 'max:1000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $isAnon = (bool) ($data['is_anonymous'] ?? false);

        $reply = Comment::create([
            'post_id'      => $comment->post_id,
            'user_id'      => Auth::id(),
            'parent_id'    => $comment->id,
            'is_anonymous' => $isAnon,
            'anon_seed'    => $isAnon ? \App\Support\AnonSeed::generate() : null,
            'body'         => $data['body'],
        ]);

        $comment->post()->increment('comments_count');
        BadgeService::onComment(Auth::user());

        // Notify parent comment author
        if ($comment->user_id !== Auth::id()) {
            $replier = Auth::user();
            $title = $isAnon ? '↩️ حد رد على كومنتك' : '↩️ '.$replier->username.' رد على كومنتك';
            \App\Services\PushService::sendToUser($comment->user_id, [
                'title' => $title,
                'body'  => \Illuminate\Support\Str::limit($data['body'], 90),
                'url'   => route('posts.show', $comment->post_id).'#comment-'.$reply->id,
                'tag'   => 'comment-reply-'.$comment->id,
            ]);
        }

        return back()->with('flash', 'تم نشر ردك.');
    }

    public function report(Comment $comment, Request $request)
    {
        $data = $request->validate([
            'reason'  => ['required', 'in:spam,abuse,nsfw,fake,other'],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        $report = Report::firstOrCreate(
            [
                'reporter_id' => Auth::id(),
                'target_type' => 'comment',
                'target_id'   => $comment->id,
            ],
            [
                'reason'  => $data['reason'],
                'details' => $data['details'] ?? null,
                'status'  => 'open',
            ]
        );

        // Auto-hide after 3 reports
        $reportCount = Report::where('target_type', 'comment')
            ->where('target_id', $comment->id)
            ->count();
        if ($reportCount >= 3 && $comment->status === 'active') {
            $comment->update(['status' => 'flagged']);
        }

        if ($report->wasRecentlyCreated) {
            \App\Services\AdminNotificationService::onReportCreated($report);
        }

        return back()->with('flash', 'تم استلام البلاغ، شكراً.');
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) abort(403);
        $comment->update(['status' => 'removed']);
        return back()->with('flash', 'تم حذف الكومنت.');
    }
}
