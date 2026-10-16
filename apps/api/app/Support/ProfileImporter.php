<?php

namespace App\Support;

use App\Models\ProfileAlias;
use App\Models\ProfileRole;
use App\Models\ProfileSocialAccount;
use App\Models\PublicProfile;
use Illuminate\Support\Facades\DB;

class ProfileImporter
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function import(array $payload): PublicProfile
    {
        return DB::transaction(function () use ($payload) {
            $profile = PublicProfile::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                [
                    'display_name' => $payload['display_name'],
                    'short_name' => $payload['short_name'] ?? null,
                    'kind' => $payload['kind'] ?? 'otro',
                    'status' => $payload['status'] ?? 'active',
                    'bio' => $payload['bio'] ?? null,
                    'metadata' => $payload['metadata'] ?? null,
                ],
            );

            if (isset($payload['aliases']) && is_array($payload['aliases'])) {
                foreach ($payload['aliases'] as $aliasRow) {
                    if (is_string($aliasRow)) {
                        $aliasRow = ['alias' => $aliasRow, 'alias_type' => 'nombre_completo'];
                    }
                    ProfileAlias::query()->updateOrCreate(
                        [
                            'profile_id' => $profile->id,
                            'alias' => ProfileText::normalizeAlias((string) $aliasRow['alias']),
                        ],
                        [
                            'alias_type' => $aliasRow['alias_type'] ?? 'nombre_completo',
                        ],
                    );
                }
            }

            $roleIds = [];
            if (isset($payload['roles']) && is_array($payload['roles'])) {
                foreach ($payload['roles'] as $roleRow) {
                    $role = ProfileRole::query()->updateOrCreate(
                        [
                            'profile_id' => $profile->id,
                            'title' => $roleRow['title'],
                            'started_on' => $roleRow['started_on'] ?? null,
                        ],
                        [
                            'organization' => $roleRow['organization'] ?? null,
                            'level' => $roleRow['level'] ?? 'otro',
                            'ended_on' => $roleRow['ended_on'] ?? null,
                        ],
                    );
                    $roleIds[$roleRow['title']] = $role->id;
                }
            }

            if (isset($payload['social_accounts']) && is_array($payload['social_accounts'])) {
                foreach ($payload['social_accounts'] as $accountRow) {
                    $roleId = null;
                    if (isset($accountRow['role_title']) && isset($roleIds[$accountRow['role_title']])) {
                        $roleId = $roleIds[$accountRow['role_title']];
                    }

                    ProfileSocialAccount::query()->updateOrCreate(
                        [
                            'profile_id' => $profile->id,
                            'platform' => ProfileText::normalizePlatform((string) $accountRow['platform']),
                            'handle' => ProfileText::normalizeHandle((string) $accountRow['handle']),
                            'started_on' => $accountRow['started_on'] ?? null,
                        ],
                        [
                            'profile_role_id' => $roleId,
                            'account_kind' => $accountRow['account_kind'] ?? 'personal',
                            'label' => $accountRow['label'] ?? null,
                            'ended_on' => $accountRow['ended_on'] ?? null,
                            'status' => $accountRow['status'] ?? 'active',
                            'use_for_avatar' => (bool) ($accountRow['use_for_avatar'] ?? false),
                        ],
                    );
                }
            }

            return $profile->fresh(['aliases', 'roles', 'socialAccounts']);
        });
    }
}
