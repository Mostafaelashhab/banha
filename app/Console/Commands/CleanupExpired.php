<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Story;
use App\Services\ImageUploader;
use Illuminate\Console\Command;

class CleanupExpired extends Command
{
    protected $signature = 'banha:cleanup
                            {--dry-run : Show what would be deleted without doing it}';

    protected $description = 'Delete expired stories, archive past events, expire stale listings (storage hygiene)';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        // ── Stories: hard-delete after expires_at (24h TTL) ──
        $storyCount = 0;
        Story::where('expires_at', '<=', now())->chunk(100, function ($stories) use (&$storyCount, $dry) {
            foreach ($stories as $s) {
                if (! $dry) {
                    ImageUploader::delete($s->image_url);
                    $s->delete();
                }
                $storyCount++;
            }
        });
        $this->info(($dry ? '[DRY] ' : '')."Deleted {$storyCount} expired stories.");

        // ── Events: auto-archive after end (or start, if no end) + 1 day ──
        $eventCount = Event::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('ends_at', '<', now()->subDay())
                  ->orWhere(function ($w) {
                      $w->whereNull('ends_at')
                        ->where('starts_at', '<', now()->subDay());
                  });
            })
            ->when(! $dry, fn ($q) => $q->update(['status' => 'archived']))
            ->when($dry,   fn ($q) => $q->count());
        $this->info(($dry ? '[DRY] ' : '')."Archived events: {$eventCount}");

        // ── Listings: expire after 60 days (uses expires_at) ──
        $listingCount = Listing::query()
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->when(! $dry, fn ($q) => $q->update(['status' => 'expired']))
            ->when($dry,   fn ($q) => $q->count());
        $this->info(($dry ? '[DRY] ' : '')."Expired listings: {$listingCount}");

        return self::SUCCESS;
    }
}
