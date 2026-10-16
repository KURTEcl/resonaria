---
name: resonaria-profiles
description: Create and link public profiles on a Resonaria portal via list_profiles and add_profile_mention MCP tools.
---

# Profiles (Resonaria MCP)

## When

A published piece names a **public-interest figure** (official, candidate, party leader, union head, journalist, expert, etc.).

## Flow

1. `list_profiles` — check `kind_options` and `creation_guide`.
2. If slug missing, create with `display_name` and `kind` via mention tool provisioning (see MCP `add_profile_mention`).
3. `add_profile_mention` on the topic slug with context.

## Quality

- Prefer official spellings; avoid duplicate slugs.
- Do not create profiles for private individuals without public role.
- Scan job (ops): `php artisan profiles:scan-published` on server when configured.

## References

- `docs/05-arquitectura/perfiles-publicos.md`
- `apps/api/config/profiles.php` for kind labels
