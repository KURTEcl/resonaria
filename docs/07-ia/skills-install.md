# Installing Resonaria agent skills

## Cursor (this machine)

```bash
./scripts/install-agent-skills.sh
```

Installs into `~/.cursor/skills/`:

- `resonaria-create-case`
- `resonaria-social-research`
- `resonaria-topic-cover`
- `resonaria-profiles`

Project copy: symlink or duplicate `skills/` into `.cursor/skills/` if your team commits project skills.

## Codex / ChatGPT

Reference skill files directly from the cloned repository (`skills/*/SKILL.md`) in custom instructions, or paste the skill body into a GPT project.

There is no global Codex skills directory equivalent to Cursor; keep skills versioned in git and point agents at `AGENTS.md`.

## After install

1. Complete `portal/bible/` via `./scripts/install-wizard.sh`
2. Connect MCP ([mcp-overview.md](mcp-overview.md))
3. Use **resonaria-create-case** only after reading `portal/bible/editorial-scope.md`
