<?php

namespace App\Http\Controllers;

use App\Http\Controllers\LocalSeoController;
use App\Models\Business;
use App\Models\Event;
use App\Models\Hashtag;
use App\Models\Listing;
use App\Models\Post;
use App\Models\Zone;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            ['/',                '1.0', 'daily'],
            ['/feed',            '0.9', 'hourly'],
            ['/directory',       '0.9', 'daily'],
            ['/market',          '0.9', 'hourly'],
            ['/events',          '0.8', 'daily'],
            ['/nearby',          '0.7', 'weekly'],
            ['/users',           '0.6', 'weekly'],
            ['/tags',            '0.6', 'daily'],
            ['/discover',        '0.7', 'daily'],
            ['/login',           '0.4', 'monthly'],
            ['/signup',          '0.6', 'monthly'],
            ['/banha-trains',     '0.85', 'daily'],   // High-traffic SEO target
            ['/banha-jobs',       '0.8',  'daily'],
            ['/banha-lost-found', '0.7',  'daily'],
            ['/emergency',        '0.7',  'weekly'],
            ['/benha-university', '0.7',  'weekly'],
            ['/offers',           '0.85', 'daily'],
            ['/bookings',         '0.85', 'daily'],
            ['/craftsmen',        '0.95', 'daily'],   // campaign destination — highest priority
            ['/craftsmen/jobs',   '0.8',  'hourly'],
            ['/zones',            '0.7',  'weekly'],
        ];

        // Per-trade landing pages — high SEO value
        foreach (['plumber','electrician','carpenter','painter','ac_tech','appliance_tech',
                 'gas_tech','aluminum','tile_setter','blacksmith','welder',
                 'glazier','locksmith','gypsum','moving','finishing','satellite_tech',
                 'pest_control','mechanic_car'] as $trade) {
            $urls[] = ['/craftsmen/'.$trade, '0.85', 'daily'];
        }

        // Categories
        foreach (['food', 'medical', 'shops', 'craftsmen', 'services'] as $cat) {
            $urls[] = ['/directory/c/'.$cat, '0.8', 'daily'];
        }

        // ── Local-SEO landing pages (the highest-intent organic targets) ──
        foreach (LocalSeoController::indexableSlugs() as $slug) {
            $urls[] = ['/'.$slug, '0.9', 'daily'];
        }

        // ── Programmatic combos: top categories × Banha city areas ──
        // (Each city neighborhood with coords + an English slug gets a few
        //  category-in-area pages.)
        $programmaticCats = ['cafes', 'doctors', 'restaurants', 'dentists', 'pharmacies'];
        try {
            $cityAreas = \App\Models\Area::banha()
                ->whereNotNull('lat')->whereNotNull('lng')
                ->whereNotNull('slug_en')
                ->orderBy('sort')->limit(20)->get(['slug_en']);
            foreach ($cityAreas as $area) {
                foreach ($programmaticCats as $cat) {
                    $urls[] = ['/'.$cat.'-in-'.$area->slug_en.'-banha', '0.7', 'weekly'];
                }
            }
        } catch (\Throwable $e) {
            // areas table may not exist yet — skip programmatic combos rather than 500 the sitemap
        }

        // Zones — both the dedicated SEO landing page and the localized feed
        foreach (Zone::where('is_active', true)->orderBy('sort')->get() as $zone) {
            $urls[] = ['/zone/'.$zone->slug, '0.85', 'daily'];
            $urls[] = ['/feed?zone='.$zone->id, '0.7', 'hourly'];
        }

        // Active businesses — use slug URL when available (brand-friendly,
        // higher CTR), fall back to numeric for any slug-less rows.
        $hasSlugCol = Schema::hasColumn('businesses', 'slug');
        $bizCols = $hasSlugCol ? ['id', 'slug', 'updated_at', 'has_menu'] : ['id', 'updated_at', 'has_menu'];
        Business::where('is_active', true)
            ->orderByDesc('has_menu')
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->limit(500)
            ->get($bizCols)
            ->each(function ($b) use (&$urls, $hasSlugCol) {
                $slug = $hasSlugCol ? ($b->slug ?? null) : null;
                $path = $slug ? '/biz/'.$slug : '/directory/business/'.$b->id;
                $urls[] = [$path, '0.85', 'weekly', $b->updated_at?->toAtomString()];
                if ($b->has_menu) {
                    // Menu pages get the highest priority — they're the SEO gold
                    $urls[] = ['/m/'.$b->id, '0.95', 'daily', $b->updated_at?->toAtomString()];
                }
            });

        // Latest 300 posts
        Post::active()->latest()->limit(300)->get(['id', 'updated_at'])->each(function ($p) use (&$urls) {
            $urls[] = ['/posts/'.$p->id, '0.6', 'weekly', $p->updated_at?->toAtomString()];
        });

        // Active listings
        Listing::where('status', 'active')->latest()->limit(200)->get(['id', 'updated_at'])->each(function ($l) use (&$urls) {
            $urls[] = ['/market/'.$l->id, '0.7', 'daily', $l->updated_at?->toAtomString()];
        });

        // Upcoming events
        Event::where('status', 'active')->where('starts_at', '>=', now())->orderBy('starts_at')->limit(100)->get(['id', 'updated_at'])->each(function ($e) use (&$urls) {
            $urls[] = ['/events/'.$e->id, '0.7', 'daily', $e->updated_at?->toAtomString()];
        });

        // Top hashtags
        Hashtag::where('uses_count', '>', 0)->orderByDesc('uses_count')->limit(50)->get(['tag', 'updated_at'])->each(function ($h) use (&$urls) {
            $urls[] = ['/tag/'.$h->tag, '0.5', 'weekly', $h->updated_at?->toAtomString()];
        });

        $base = rtrim(url('/'), '/');
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $row) {
            [$path, $priority, $freq] = [$row[0], $row[1], $row[2]];
            $lastmod = $row[3] ?? null;
            $xml .= "  <url>\n";
            $xml .= "    <loc>".htmlspecialchars($base.$path, ENT_XML1 | ENT_QUOTES)."</loc>\n";
            if ($lastmod) $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>{$freq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** robots.txt with Google-friendly directives. */
    public function robots(): Response
    {
        $body  = "User-agent: *\n";
        $body .= "Allow: /\n";
        $body .= "Disallow: /admin\n";
        $body .= "Disallow: /me\n";
        $body .= "Disallow: /chat\n";
        $body .= "Disallow: /verify\n";
        $body .= "Disallow: /forgot\n";
        $body .= "\n";
        $body .= "Sitemap: ".url('/sitemap.xml')."\n";
        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
