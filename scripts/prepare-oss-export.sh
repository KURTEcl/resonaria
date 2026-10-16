#!/usr/bin/env bash
# Build a clean export tree for the public github.com/KURTEcl/resonaria repository.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEST="${1:-$ROOT/../resonaria-export}"
EXCLUDES="$ROOT/oss-export-excludes.txt"

if [[ ! -f "$EXCLUDES" ]]; then
  echo "Missing oss-export-excludes.txt"
  exit 1
fi

rm -rf "$DEST"
mkdir -p "$DEST"

rsync -a \
  --exclude-from="$EXCLUDES" \
  "$ROOT"/ "$DEST"/

echo "Export ready at: $DEST"
echo "Review secrets: rg -i 'password|token|BEGIN PRIVATE|45\\.228' \"$DEST\" || true"
echo "File count: $(find "$DEST" -type f | wc -l | tr -d ' ')"
