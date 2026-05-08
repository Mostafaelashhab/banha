<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    /**
     * Show the activation page (after signup, while tier=none).
     */
    public function showActivate()
    {
        $user = Auth::user();
        if (in_array($user->verification_tier, ['bronze', 'silver', 'gold'], true)) {
            return redirect()->route('feed');
        }
        return view('auth.verify', [
            'user'    => $user,
            'purpose' => OtpService::PURPOSE_ACTIVATE,
        ]);
    }

    public function sendActivate(Request $request)
    {
        $user   = Auth::user();
        $result = OtpService::send($user->phone, OtpService::PURPOSE_ACTIVATE, $request->ip());

        $msg = $result['sent']
            ? '✓ بعتنالك كود تفعيل على واتساب.'
            : 'في مشكلة بسيطة في إرسال الكود — حاول تاني بعد دقيقة.';

        $request->session()->flash('flash', $msg);

        if (app()->isLocal() && ! empty($result['code'])) {
            $request->session()->flash('debug_otp', $result['code']);
        }

        return back();
    }

    public function verifyActivate(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();

        if (! OtpService::verify($user->phone, OtpService::PURPOSE_ACTIVATE, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'الكود غلط أو انتهت صلاحيته. اطلب كود جديد.',
            ]);
        }

        VerificationService::markBronzeOnSignup($user);

        return redirect()->route('feed')->with('flash', '🎉 حسابك اتفعّل! مبروك يا '.$user->username);
    }

    /**
     * Forgot-password flow.
     */
    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendForgot(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
        ], ['phone.regex' => 'لازم رقم موبايل مصري صحيح.']);

        $user = User::where('phone', $data['phone'])->first();
        if (! $user) {
            // don't leak existence — just pretend success
            return redirect()->route('forgot.verify', ['phone' => $data['phone']])
                ->with('flash', 'لو الرقم مسجّل، الكود وصل على واتساب.');
        }

        $result = OtpService::send($user->phone, OtpService::PURPOSE_PASSWORD, $request->ip());

        $session = session();
        if (app()->isLocal() && ! empty($result['code'])) {
            $session->flash('debug_otp', $result['code']);
        }

        return redirect()->route('forgot.verify', ['phone' => $data['phone']])
            ->with('flash', 'لو الرقم مسجّل، الكود وصل على واتساب.');
    }

    public function showForgotVerify(Request $request)
    {
        $phone = $request->query('phone');
        if (! $phone) return redirect()->route('forgot');
        return view('auth.forgot-verify', ['phone' => $phone]);
    }

    public function verifyForgot(Request $request)
    {
        $data = $request->validate([
            'phone'    => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'code'     => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'max:80', 'confirmed'],
        ], [
            'password.confirmed' => 'الباسورد مش متطابق.',
            'password.min'       => 'الباسورد لازم ٦ حروف على الأقل.',
        ]);

        if (! OtpService::verify($data['phone'], OtpService::PURPOSE_PASSWORD, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'الكود غلط أو انتهت صلاحيته.',
            ]);
        }

        $user = User::where('phone', $data['phone'])->first();
        if ($user) {
            $user->update(['password' => $data['password']]);
        }

        return redirect()->route('login')->with('flash', '✓ تم تغيير الباسورد. ادخل بالباسورد الجديد.');
    }
}
