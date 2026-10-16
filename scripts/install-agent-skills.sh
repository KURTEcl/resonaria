#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SKILLS_SRC="$ROOT/skills"
CURSOR_DEST="${HOME}/.cursor/skills"
CODEX_DOCS="$ROOT/docs/07-ia/skills-install.md"

if [[ ! -d "$SKILLS_SRC" ]]; then
  echo "No skills/ directory in $ROOT"
  exit 1
fi

mkdir -p "$CURSOR_DEST"
for dir in "$SKILLS_SRC"/resonaria-*; do
  [[ -d "$dir" ]] || continue
  name="$(basename "$dir")"
  rm -rf "$CURSOR_DEST/$name"
  cp -R "$dir" "$CURSOR_DEST/$name"
  echo "Installed Cursor skill: $name → $CURSOR_DEST/$name"
done

echo ""
echo "Codex / ChatGPT: skills are referenced from the repo. See $CODEX_DOCS"
