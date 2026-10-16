<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portal identity (self-host / open source)
    |--------------------------------------------------------------------------
    |
    | Set during install via scripts/install-wizard.sh or php artisan portal:init.
    | Nuxt reads the NUXT_PUBLIC_* mirrors at build time; this config drives API
    | responses and generated portal/bible/*.md files.
    |
    */

    'name' => env('PORTAL_NAME', env('APP_NAME', 'Resonaria')),

    'tagline' => env('PORTAL_TAGLINE', 'What changes. Who is affected.'),

    'description' => env('PORTAL_DESCRIPTION', 'An open, source-driven publication portal.'),

    'locale' => env('PORTAL_LOCALE', 'es'),

    'html_lang' => env('PORTAL_HTML_LANG', 'es'),

    'github_url' => env('PORTAL_GITHUB_URL', 'https://github.com/kurtecl/resonaria'),

    'support_url' => env('PORTAL_SUPPORT_URL', 'https://ko-fi.com/kurtecl'),

    'social_instagram' => env('PORTAL_SOCIAL_INSTAGRAM', ''),

    'social_x' => env('PORTAL_SOCIAL_X', ''),

    'content_types' => array_values(array_filter(array_map(
        static fn (string $v): string => trim($v),
        explode(',', (string) env('PORTAL_CONTENT_TYPES', 'tracking,news,analysis,opinion')),
    ))),

    'editorial_tone' => env('PORTAL_EDITORIAL_TONE', 'clear-formal'),

    'agent_email' => env('PORTAL_AGENT_EMAIL', 'agent@portal.local'),

    'bible_path' => env('PORTAL_BIBLE_PATH', base_path('../../portal/bible')),

];
