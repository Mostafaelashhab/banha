<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\BusinessReview;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ImportFirebaseExtras extends Command
{
    protected $signature = 'banha:import-firebase-extras
                            {--base=https://banha-restaurants.firebaseio.com : RTDB base URL}
                            {--reviews : Import comments as reviews}
                            {--sponsors : Mark sponsor entries as verified}
                            {--all : Import everything (reviews + sponsors)}
                            {--limit=0 : Limit number of restaurants processed for reviews (0 = no limit)}';

    protected $description = 'Import additional Firebase data: sponsor flags + customer reviews';

    public function handle(): int
    {
        $doReviews  = $this->option('reviews')  || $this->option('all');
        $doSponsors = $this->option('sponsors') || $this->option('all');

        if (! $doReviews && ! $doSponsors) {
            $this->error('Pick at least one: --reviews, --sponsors, or --all');
            return self::FAILURE;
        }

        if ($doSponsors) {
            $this->importSponsors();
        }

        if ($doReviews) {
            $this->importReviews();
        }

        return self::SUCCESS;
    }

    private function importSponsors(): void
    {
        $url = rtrim($this->option('base'), '/').'/sponsor.json';
        $this->info("→ Fetching sponsors: {$url}");

        $resp = Http::timeout(30)->get($url);
        if (! $resp->successful()) {
            $this->error('Sponsors fetch failed: HTTP '.$resp->status());
            return;
        }

        $data = $resp->json() ?? [];
        $matched = 0; $missed = [];

        foreach ($data as $key => $entry) {
            if (! is_array($entry) || empty($entry['restaurant'])) continue;

            $candidates = array_filter([
                $entry['restaurant'] ?? null,
                $key,
            ]);

            $business = null;
            foreach ($candidates as $name) {
                $name = trim($name);
                $business = Business::where('name', 'LIKE', "%{$name}%")
                    ->orWhere('name', $name)
                    ->first();
                if ($business) break;
            }

            if (! $business) {
                $missed[] = $entry['restaurant'];
                continue;
            }

            $business->update([
                'is_verified' => true,
            ]);
            $matched++;
            $this->line("  ✓ {$business->name} → verified");
        }

        $this->info("Sponsors matched: {$matched}/".count($data));
        if ($missed) {
            $this->warn('Unmatched: '.implode(', ', $missed));
        }
    }

    private function importReviews(): void
    {
        $url = rtrim($this->option('base'), '/').'/restaurant.json';
        $this->info("→ Fetching restaurants for reviews: {$url}");

        $resp = Http::timeout(120)->get($url);
        if (! $resp->successful()) {
            $this->error('Restaurants fetch failed: HTTP '.$resp->status());
            return;
        }

        $data = $resp->json() ?? [];
        $limit = (int) $this->option('limit');

        $stats = [
            'restaurants_scanned' => 0,
            'restaurants_matched' => 0,
            'reviews_inserted'    => 0,
            'reviews_skipped'     => 0,
        ];

        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $key => $entry) {
            $bar->advance();
            $stats['restaurants_scanned']++;

            if (! is_array($entry) || empty($entry['comment']) || ! is_array($entry['comment'])) continue;
            if (($entry['isDelete'] ?? null) === 'yes') continue;

            // Find local business by name (try Firebase 'name' field, then key)
            $name     = trim($entry['name'] ?? $key);
            $business = Business::where('name', $name)->first();
            if (! $business) {
                $business = Business::where('name', 'LIKE', "%{$name}%")->first();
            }
            if (! $business) continue;

            $stats['restaurants_matched']++;

            foreach ($entry['comment'] as $cid => $c) {
                if (! is_array($c)) continue;

                $body = trim((string) ($c['desc'] ?? ''));
                $rate = (int) ($c['rate'] ?? 0);

                // Skip empty + zero-rated reviews (noise)
                if ($body === '' && $rate === 0) {
                    $stats['reviews_skipped']++;
                    continue;
                }

                $extId = (string) $cid;

                $exists = BusinessReview::where('business_id', $business->id)
                    ->where('external_id', $extId)
                    ->exists();
                if ($exists) {
                    $stats['reviews_skipped']++;
                    continue;
                }

                $reviewedAt = $this->parseArabicDate((string) ($c['date'] ?? ''));

                BusinessReview::create([
                    'business_id'  => $business->id,
                    'user_id'      => null,
                    'author_name'  => null,
                    'author_phone' => $this->cleanPhone($c['phone'] ?? null),
                    'rating'       => max(0, min(5, $rate)),
                    'body'         => mb_substr($body, 0, 1000) ?: null,
                    'source'       => 'firebase',
                    'external_id'  => $extId,
                    'reviewed_at'  => $reviewedAt,
                ]);
                $stats['reviews_inserted']++;
            }

            // Recalculate ratings_count from reviews (keep existing rating_avg from rate1..rate5)
            $newCount = BusinessReview::where('business_id', $business->id)->count();
            if ($newCount > $business->ratings_count) {
                $business->update(['ratings_count' => $newCount]);
            }

            if ($limit > 0 && $stats['restaurants_matched'] >= $limit) break;
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Metric', 'Count'], [
            ['Restaurants scanned', $stats['restaurants_scanned']],
            ['Restaurants matched', $stats['restaurants_matched']],
            ['Reviews inserted',    $stats['reviews_inserted']],
            ['Reviews skipped',     $stats['reviews_skipped']],
        ]);
    }

    private function parseArabicDate(?string $s): ?Carbon
    {
        if (! $s) return null;

        // Convert Arabic-Indic digits to ASCII
        $map = ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'];
        $s = strtr(trim($s), $map);

        try {
            return Carbon::parse($s);
        } catch (\Throwable) {
            return null;
        }
    }

    private function cleanPhone(?string $s): ?string
    {
        if (! $s) return null;
        $s = preg_replace('/\D/', '', $s);
        return $s ? mb_substr($s, 0, 20) : null;
    }
}
