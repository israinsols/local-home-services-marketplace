<?php

return [
    'name' => env('MARKETPLACE_NAME', 'Local Home Services'),
    'contact_email' => env('SITE_CONTACT_EMAIL'),
    'lead_consent_version' => env('LEAD_CONSENT_VERSION', '2026-01'),
    'credit_packs' => [
        'starter' => ['credits' => 50, 'price_cents' => 2500],
        'growth' => ['credits' => 125, 'price_cents' => 5000],
        'scale' => ['credits' => 300, 'price_cents' => 10000],
    ],
];
