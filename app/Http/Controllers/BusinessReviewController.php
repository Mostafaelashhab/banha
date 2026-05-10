<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class BusinessReviewController extends Controller
{
    public function store(Request $request, Business $business)
    {
        $u = Auth::user();

        if ($business->owner_user_id === $u->id) {
            return back()->with('error', 'مينفعش تقيّم نشاطك.');
        }

        $key = 'review:'.$u->id.':'.$business->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages(['rating' => 'حاول كمان شوية.']);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body'   => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($business, $u, $data) {
            BusinessReview::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $u->id, 'source' => 'user'],
                [
                    'rating'       => $data['rating'],
                    'body'         => $data['body'] ?? null,
                    'author_name'  => $u->name ?? $u->username,
                    'author_phone' => $u->phone ?? null,
                    'reviewed_at'  => now(),
                ]
            );

            $stats = BusinessReview::where('business_id', $business->id)
                ->where('rating', '>', 0)
                ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
                ->first();

            $business->update([
                'rating_avg'    => round((float) ($stats->avg ?? 0), 1),
                'ratings_count' => (int) ($stats->cnt ?? 0),
            ]);
        });

        return redirect()->route('directory.show', $business)
            ->with('ok', 'شكراً على تقييمك!');
    }

    public function destroy(Business $business)
    {
        $u = Auth::user();

        DB::transaction(function () use ($business, $u) {
            BusinessReview::where('business_id', $business->id)
                ->where('user_id', $u->id)
                ->where('source', 'user')
                ->delete();

            $stats = BusinessReview::where('business_id', $business->id)
                ->where('rating', '>', 0)
                ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
                ->first();

            $business->update([
                'rating_avg'    => round((float) ($stats->avg ?? 0), 1),
                'ratings_count' => (int) ($stats->cnt ?? 0),
            ]);
        });

        return back()->with('ok', 'تم حذف التقييم.');
    }
}
