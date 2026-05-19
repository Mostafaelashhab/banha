<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessPhotoController extends Controller
{
    /** Default gallery cap. Craftsmen get a higher cap (portfolio is their main selling point). */
    public const MAX_PHOTOS_DEFAULT  = 6;
    public const MAX_PHOTOS_CRAFTSMEN = 12;

    public static function maxPhotos(Business $business): int
    {
        return $business->category === 'craftsmen'
            ? self::MAX_PHOTOS_CRAFTSMEN
            : self::MAX_PHOTOS_DEFAULT;
    }

    public function store(Business $business, Request $request)
    {
        $this->authorize($business);

        $max   = self::maxPhotos($business);
        $count = $business->photos()->count();
        if ($count >= $max) {
            return back()->withErrors(['photo' => 'الحد الأقصى ' . $max . ' صور.']);
        }

        $data = $request->validate([
            'photo'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:120'],
        ]);

        $url = ImageUploader::store($data['photo'], 'business-gallery');
        if (! $url) return back()->withErrors(['photo' => 'فشل رفع الصورة.']);

        BusinessPhoto::create([
            'business_id' => $business->id,
            'url'         => $url,
            'caption'     => $data['caption'] ?? null,
            'sort'        => $count,
        ]);

        return back()->with('flash', '✓ الصورة اتضافت');
    }

    /** Update just the caption of an existing photo (no re-upload). */
    public function updateCaption(BusinessPhoto $photo, Request $request)
    {
        $this->authorize($photo->business);
        $data = $request->validate(['caption' => ['nullable', 'string', 'max:120']]);
        $photo->update(['caption' => $data['caption'] ?? null]);
        return back()->with('flash', '✓ تم التحديث');
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
