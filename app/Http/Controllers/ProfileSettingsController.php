<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5025'],
        ], [
            'avatar.image' => 'الملف لازم يكون صورة.',
            'avatar.mimes' => 'لازم JPG / PNG / WEBP.',
            'avatar.max'   => 'حجم الصورة لازم أقل من ٢ ميجا.',
        ]);

        $user = Auth::user();

        // Delete old
        if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
            $relative = ltrim(str_replace('/storage/', '', $user->avatar_url), '/');
            Storage::disk('public')->delete($relative);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_url' => '/storage/'.$path]);

        return back()->with('flash', '✓ صورتك اتغيّرت.');
    }

    public function deleteAvatar()
    {
        $user = Auth::user();
        if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
            $relative = ltrim(str_replace('/storage/', '', $user->avatar_url), '/');
            Storage::disk('public')->delete($relative);
        }
        $user->update(['avatar_url' => null]);
        return back()->with('flash', 'تم حذف الصورة.');
    }
}
