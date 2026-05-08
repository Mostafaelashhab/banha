<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Zone;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            ['/',         '1.0', 'daily'],
            ['/login',    '0.5', 'monthly'],
            ['/signup',   '0.7', 'monthly'],
        ];

        foreach (Zone::where('is_active', true)->orderBy('sort')->get() as $zone) {
            $urls[] = ['/feed?zone='.$zone->id, '0.8', 'hourly'];
        }

        // Latest 200 posts
        Post::active()->latest()->limit(200)->get(['id', 'updated_at'])->each(function ($p) use (&$urls) {
            $urls[] = ['/posts/'.$p->id, '0.7', 'weekly', $p->updated_at?->toAtomString()];
        });

        $base = url('/');
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $row) {
            [$path, $priority, $freq] = [$row[0], $row[1], $row[2]];
            $lastmod = $row[3] ?? null;
            $xml .= "  <url>\n";
            $xml .= "    <loc>".htmlspecialchars($base.$path, ENT_XML1)."</loc>\n";
            if ($lastmod) {
                $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$freq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
