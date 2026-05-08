<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileSettingsController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
            'persona' => ['required', 'in:student,worker,homemaker,merchant,resident'],
        ]);

        $user->fill($data)->save();

        return back()->with('flash', 'تم تحديث البيانات.');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'max:80', 'confirmed', 'different:current_password'],
        ], [
            'password.confirmed'  => 'الباسورد الجديد مش متطابق.',
            'password.different'  => 'الباسورد الجديد لازم يبقى مختلف.',
            'password.min'        => 'الباسورد لازم ٦ حروف على الأقل.',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'الباسورد الحالي غلط.',
            ]);
        }

        $user->update(['password' => $data['password']]);

        return back()->with('flash', 'تم تغيير الباسورد.');
    }
}
