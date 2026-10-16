# Publishing the public GitHub repository

The canonical open-source name is **`resonaria`**: [https://github.com/kurtecl/resonaria](https://github.com/kurtecl/resonaria)

## Export a clean tree

From the development monorepo:

```bash
chmod +x scripts/prepare-oss-export.sh
./scripts/prepare-oss-export.sh ../resonaria-export
cd ../resonaria-export
```

Review for secrets:

```bash
rg -i 'password|token|BEGIN PRIVATE|45\.228' . || true
```

## Create or push GitHub repo

```bash
git init
git add .
git commit -m "Initial public release"
gh repo create kurtecl/resonaria --public --source=. --remote=origin --push
```

If the repo already exists:

```bash
git remote add origin https://github.com/kurtecl/resonaria.git
git push -u origin main
```

## What stays private

- Real case folders under `docs/casos/` with production URLs
- Video assets and social campaign WIP
- Server IPs, CloudPanel users, `db-pull-from-prod.sh` credentials
- Editorial prompts tied to a single country's politics (use `portal/bible/` per instance instead)

## Sync strategy

Recommended: develop features in the private instance repo, export or merge into `resonaria` on release tags.
