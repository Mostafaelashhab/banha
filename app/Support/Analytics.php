<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Single funnel for all client-tracked events. The current implementation logs
 * to the standard Laravel log channel so events are visible during development
 * without wiring up a paid analytics provider. Swap the `dispatch()` body when
 * a real provider (PostHog / Plausible / etc.) gets picked.
 *
 * Client-side, the existing `data-track-click="…"` attributes on CTAs (call,
 * whatsapp, directions, claim, report, offer, etc.) are the source of truth.
 * `public/js/track.js` (loaded by the layout) POSTs them here via /track.
 *
 * Server-side, controllers can call Analytics::track('search_performed', [...])
 * directly.
 */
class Analytics
{
    /**
     * Canonical event names. Defined explicitly so typos in views surface as
     * "unknown event" rather than getting silently dropped into analytics
     * with whatever string a copy-paste pulled in.
     */
    public const EVENTS = [
        'business_call'           => 'Phone clicked on a business card or page',
        'business_whatsapp'       => 'WhatsApp clicked on a business card or page',
        'business_directions'     => 'Directions clicked on a business card or page',
        'business_claim'          => 'Claim-this-page clicked',
        'business_report'         => 'Report-wrong-data clicked',
        'offer_clicked'           => 'An offer card was opened',
        'qr_menu_cta_clicked'     => 'QR-menu sales-page CTA clicked',
        'marketplace_contact'     => 'Contact / WhatsApp clicked on a marketplace listing',
        'marketplace_promote'     => 'Promote-listing CTA clicked',
        'search_performed'        => 'Homepage / nav search submitted',
        'category_clicked'        => 'Category tile clicked',
        'open_now_filter'         => 'Open-now category filter clicked',
    ];

    /**
     * Public entry point. Unknown event names are dropped with a single log
     * line so we notice without leaking junk events into the funnel.
     */
    public static function track(string $event, array $payload = []): void
    {
        if (! array_key_exists($event, self::EVENTS)) {
            Log::warning('[analytics] unknown event', ['event' => $event, 'payload' => $payload]);
            return;
        }

        self::dispatch($event, $payload);
    }

    private static function dispatch(string $event, array $payload): void
    {
        // Replace this method's body when a real provider gets wired in.
        // The current shape is intentionally "log and forget" — enough to verify
        // wiring without a paid provider in the loop.
        Log::channel(config('logging.default'))->info('[analytics] '.$event, $payload);
    }
}
