<?php

/**
 * Paid-verified-badge configuration.
 *
 * Pricing + payment destinations are sourced from .env so we can change them
 * without redeploying. Defaults are sensible for local dev/staging.
 *
 *   VERIFIED_PRICE_EGP=150          # one-year badge
 *   VERIFIED_DURATION_MONTHS=12
 *   VERIFIED_INSTAPAY=banhashop@instapay
 *   VERIFIED_VODAFONE=01000000000
 *   VERIFIED_CASH_CONTACT=01000000000   # WhatsApp number to coordinate cash pickup
 *   VERIFIED_ADMIN_WHATSAPP=01000000000 # used in the "Cash" instructions
 */
return [
    'price_egp'        => (int) env('VERIFIED_PRICE_EGP', 150),
    'duration_months'  => (int) env('VERIFIED_DURATION_MONTHS', 12),

    'instapay_handle'  => env('VERIFIED_INSTAPAY', 'banhashop@instapay'),
    'vodafone_number'  => env('VERIFIED_VODAFONE', '01000000000'),
    'cash_whatsapp'    => env('VERIFIED_CASH_CONTACT', env('VERIFIED_ADMIN_WHATSAPP', '01000000000')),
    'admin_whatsapp'   => env('VERIFIED_ADMIN_WHATSAPP', '01000000000'),
];
