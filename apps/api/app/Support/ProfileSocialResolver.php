<?php

namespace App\Support;

use App\Models\ProfileSocialAccount;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ProfileSocialResolver
{
    /**
     * @return Collection<int, ProfileSocialAccount>
     */
    public static function accountsForHandle(string $platform, string $handle): Collection
    {
        $platform = ProfileText::normalizePlatform($platform);
        $handle = ProfileText::normalizeHandle($handle);

        return ProfileSocialAccount::query()
            ->where('platform', $platform)
            ->where('handle', $handle)
            ->orderByDesc('started_on')
            ->get();
    }

    public static function resolveProfileId(string $platform, string $handle, ?CarbonInterface $on = null): ?int
    {
        $on = ($on ?? now())->copy()->startOfDay();
        $accounts = self::accountsForHandle($platform, $handle);

        foreach ($accounts as $account) {
            if ($account->isActiveOn($on)) {
                return $account->profile_id;
            }
        }

        return $accounts->first()?->profile_id;
    }

    public static function avatarSourceAccount(int $profileId): ?ProfileSocialAccount
    {
        $accounts = ProfileSocialAccount::query()
            ->where('profile_id', $profileId)
            ->where('status', 'active')
            ->get();

        $marked = $accounts->first(fn (ProfileSocialAccount $a) => $a->use_for_avatar && $a->platform === 'x' && $a->isActiveOn());
        if ($marked) {
            return $marked;
        }

        $personal = $accounts->first(fn (ProfileSocialAccount $a) => $a->platform === 'x'
            && $a->account_kind === 'personal'
            && $a->isActiveOn());

        if ($personal) {
            return $personal;
        }

        return $accounts->first(fn (ProfileSocialAccount $a) => $a->platform === 'x' && $a->isActiveOn());
    }
}
