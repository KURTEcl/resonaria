# Gobernanza open source

## Licencia del software

**MIT** — ver [LICENSE](../../LICENSE). Permite uso comercial y modificaciones; no impone copyleft en instancias desplegadas.

## Licencia del contenido editorial

Cada operador de una instancia define la licencia del **contenido publicado** (recomendado declarar en `/metodologia`, p. ej. CC BY 4.0). El software y el contenido son independientes.

## Mantenedores

- Definir nombres y canal de contacto al publicar el repositorio remoto.
- Releases etiquetados en git; notas de migración para self-host.

## Crawlers de IA

Decisión por instancia:

- **Indexación clásica** (Google/Bing): recomendado para descubrimiento.
- **Bots de entrenamiento** (GPTBot, ClaudeBot, Google-Extended): opcional bloquear en `robots.txt`; si se bloquean, mantener [`/llms.txt`](../../public/llms.txt) con reglas de citación.

Documentar la postura elegida en metodología pública.

## Conflictos de interés

Patrocinadores y aportes no controlan fichas (plan v1.0 §14.5).
