<?php

namespace App\Http\Controllers;

use App\Support\SiteDiscovery;
use Illuminate\Http\Response;

class DiscoveryController extends Controller
{
    public function sitemap(): Response
    {
        return $this->payload(SiteDiscovery::sitemapXml(), 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return $this->payload(SiteDiscovery::robotsTxt(), 'text/plain; charset=UTF-8');
    }

    public function llms(): Response
    {
        return $this->payload(SiteDiscovery::llmsTxt(), 'text/plain; charset=UTF-8');
    }

    public function llmsFull(): Response
    {
        return $this->payload(SiteDiscovery::llmsFullTxt(), 'text/plain; charset=UTF-8');
    }

    public function googleVerification(string $token): Response
    {
        $expected = trim((string) config('app.google_site_verification'));
        abort_unless($expected !== '' && hash_equals($expected, $token), 404);

        return $this->payload('google-site-verification: google'.$expected.".html\n", 'text/html; charset=UTF-8');
    }

    private function payload(string $body, string $type): Response
    {
        return response($body, 200, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
