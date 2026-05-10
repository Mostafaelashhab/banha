<?php

namespace App\Http\Controllers;

use App\Models\ChatThread;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Live support chat: opens (or creates) a thread between the user
 * and the dedicated بنهاوي · الدعم account, then redirects there.
 *
 * Guests see a fallback page with WhatsApp/email contact instead.
 */
class SupportController extends Controller
{
    public const SUPPORT_USERNAME = 'banhawy_support';

    public function open()
    {
        // Guests → fallback page with WhatsApp/email contact
        if (! Auth::check()) {
            return view('support.guest');
        }

        $admin = $this->primaryAdmin();

        // No admin in the system at all → guest fallback (rare edge case)
        if (! $admin) {
            return view('support.guest');
        }

        // Admin opening their own support → just go to their inbox to read tickets
        if (Auth::id() === $admin->id) {
            return redirect()->route('chat.inbox');
        }

        $thread = ChatThread::between(Auth::id(), $admin->id);
        return redirect()->route('chat.show', $thread)
            ->with('flash', 'فريق الدعم بيرد عادة خلال أقل من ساعة. ابعت رسالتك.');
    }

    /**
     * Pick the admin who handles support tickets.
     * Order:
     *   1. Real admin (is_admin = true), excluding the legacy placeholder user.
     *   2. Lowest id wins → consistent target across calls.
     */
    private function primaryAdmin(): ?User
    {
        return User::where('is_admin', true)
            ->where('is_banned', false)
            ->where('username', '!=', self::SUPPORT_USERNAME)
            ->orderBy('id')
            ->first();
    }
}
