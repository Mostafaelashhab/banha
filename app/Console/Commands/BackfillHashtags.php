<?php

namespace App\Console\Commands;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Console\Command;

class BackfillHashtags extends Command
{
    protected $signature = 'banha:backfill-hashtags';
    protected $description = 'Re-extract #hashtags from existing post titles + bodies and rebuild the index';

    public function handle(): int
    {
        $total = Post::where('status', 'active')->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $tagged = 0;
        Post::where('status', 'active')->chunk(100, function ($posts) use (&$tagged, $bar) {
            foreach ($posts as $p) {
                $text = ($p->title ?? '').' '.$p->body;
                $before = $p->hashtags()->count();
                Hashtag::syncForPost($p, $text);
                if ($p->hashtags()->count() > 0) $tagged++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Posts with at least one hashtag: {$tagged} / {$total}");
        $this->info('Total unique tags: '.Hashtag::count());

        return self::SUCCESS;
    }
}
