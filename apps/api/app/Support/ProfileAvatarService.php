<?php

namespace App\Support;

use App\Models\PublicProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileAvatarService
{
    public function refresh(PublicProfile $profile): bool
    {
        $account = ProfileSocialResolver::avatarSourceAccount($profile->id);
        if (! $account) {
            return false;
        }

        $remoteUrl = $this->fetchXProfileImageUrl($account->handle);
        if (! $remoteUrl) {
            return false;
        }

        $response = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; Resonaria/1.0; +https://resonaria.cl)',
            ])
            ->get($remoteUrl);

        if (! $response->successful()) {
            return false;
        }

        $extension = Str::contains($remoteUrl, '.png') ? 'png' : 'jpg';
        $path = 'profiles/'.$profile->slug.'.'.$extension;

        Storage::disk('public')->put($path, $response->body());

        $publicUrl = Storage::disk('public')->url($path);

        $account->forceFill([
            'avatar_remote_url' => $remoteUrl,
            'avatar_fetched_at' => now(),
        ])->save();

        $profile->forceFill([
            'photo_url' => $publicUrl,
            'photo_source_account_id' => $account->id,
            'photo_updated_at' => now(),
        ])->save();

        return true;
    }

    public function fetchXProfileImageUrl(string $handle): ?string
    {
        $handle = ProfileText::normalizeHandle($handle);
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; Resonaria/1.0; +https://resonaria.cl)',
                'Accept' => 'text/html',
            ])
            ->get('https://x.com/'.$handle);

        if (! $response->successful()) {
            return null;
        }

        $html = $response->body();
        if (preg_match('/property="og:image"\s+content="([^"]+)"/i', $html, $match)) {
            $url = html_entity_decode($match[1], ENT_QUOTES);
            if (str_contains($url, 'profile_images')) {
                return $url;
            }
        }

        if (preg_match('/https:\/\/pbs\.twimg\.com\/profile_images\/[^"\'\s]+/i', $html, $match)) {
            return $match[0];
        }

        return null;
    }
}
