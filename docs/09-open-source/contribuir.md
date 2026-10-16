# Cómo contribuir

## Qué no commitear

Revisa [../../.gitignore](../../.gitignore) antes de cada commit:

| Nunca | Motivo |
| --- | --- |
| `.env`, claves API, tokens MCP | Secretos |
| `vendor/`, `node_modules/` | Dependencias instalables |
| `.DS_Store`, `.cursor/` | Ruido local |
| `*.sqlite`, `deploy/data/` | Datos locales |
| PDFs o exports con datos personales | Privacidad |
| Borradores editoriales privados | Usar `docs/interno/` (ignorado) si hace falta |

Comando útil: `git status` y `git check-ignore -v <ruta>`.

## Flujo git

1. Fork o rama feature.
2. Cambios acotados + tests si aplica.
3. PR con descripción y enlace a issue.
4. Un mantenedor revisa; CI (futuro) debe pasar.

## Documentación

- Cambios de API → `docs/05-arquitectura/contratos-api.md` + OpenAPI.
- Cambios editoriales → `docs/03-metodologia/`.
- Agentes → `docs/agents/`.

## Estilo

- PHP/Laravel y TypeScript/Nuxt: seguir convenciones del proyecto al crearse `apps/`.
- Prosa en español en `docs/`.

## Apoyar el proyecto

Mantenimiento del software open source: [Ko-fi — kurtecl](https://ko-fi.com/kurtecl)

## Preguntas

Abrir discusión en [github.com/kurtecl/resonaria](https://github.com/kurtecl/resonaria/issues).
