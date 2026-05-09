<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessPhotoController extends Controller
{
    public const MAX_PHOTOS = 6;

    public function store(Business $business, Request $request)
    {
        $this->authorize($business);

        $count = $business->photos()->count();
        if ($count >= self::MAX_PHOTOS) {
            return back()->withErrors(['photo' => 'الحد الأقصى '.self::MAX_PHOTOS.' صور.']);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $url = ImageUploader::store($request->file('photo'), 'business-gallery');
        if (! $url) return back()->withErrors(['photo' => 'فشل رفع الصورة.']);

        BusinessPhoto::create([
            'business_id' => $business->id,
            'url'         => $url,
            'sort'        => $count,
        ]);

        return back()->with('flash', '✓ الصورة اتضافت');
    }

    public function destroy(BusinessPhoto $photo)
    {
        $this->authorize($photo->business);
        ImageUploader::delete($photo->url);
        $photo->delete();
        return back()->with('flash', 'الصورة اتمسحت');
    }

    private function authorize(Business $b): void
    {
        $u = Auth::user();
        if (! $u) abort(401);
        if (! $u->is_admin && $b->owner_user_id !== $u->id) abort(403);
    }
}
