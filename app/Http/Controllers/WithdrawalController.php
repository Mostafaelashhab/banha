<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    /** User submits a withdrawal request. */
    public function store(Request $request)
    {
        $key = 'withdraw:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'amount' => 'حاولت كتير. استنى دقايق.',
            ]);
        }
        RateLimiter::hit($key, 300);

        $data = $request->validate([
            'amount_egp'    => ['required', 'integer', 'min:'.WithdrawalService::MIN_EGP, 'max:'.WithdrawalService::MAX_EGP],
            'method'        => ['required', 'in:instapay,vcash'],
            'payout_handle' => ['required', 'string', 'min:11', 'max:20'],
        ]);

        WithdrawalService::request(
            Auth::user(),
            (int) $data['amount_egp'],
            $data['method'],
            $data['payout_handle']
        );

        return redirect()->route('profile.me', ['tab' => 'points'])
            ->with('flash', '✓ طلبك اتسجّل وهيراجعه فريق بنهاوي خلال ٤٨ ساعة.');
    }

    /** User cancels their own pending request. */
    public function cancel(Withdrawal $withdrawal)
    {
        WithdrawalService::cancel($withdrawal, Auth::user());
        return back()->with('flash', 'تم إلغاء طلب السحب.');
    }
}
