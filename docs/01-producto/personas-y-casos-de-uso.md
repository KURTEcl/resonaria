# Personas y casos de uso

Resonaria atiende tanto a quien busca informarse sobre la actualidad como a quien quiere entender el contexto, leer análisis u opiniones, o seguir una decisión pública con evidencia.

## Lectora o lector

- Encuentra noticias nacionales e internacionales y distingue rápidamente noticia, análisis y opinión.
- Consulta contexto, autoría, fecha y fuentes de cada pieza.
- Sigue decisiones públicas con antecedentes, cronología y estado actualizado.
- Comparte enlaces con preview correcto (OG).
- No necesita cuenta.

## Editora o editor

- Crea y actualiza expedientes en borrador.
- Adjunta fuentes y afirmaciones tipadas.
- Envía a revisión; no publica sin permiso.

## Revisora o revisor

- Aprueba o rechaza borradores.
- Solicita correcciones; registra cambios materiales.
- Publica versiones con trazabilidad.

## Operador técnico (self-host)

- Despliega Laravel + Nuxt + Postgres + Redis.
- Configura backups, HTTPS, tokens de agente y MCP opcional.
- No modifica criterios editoriales sin acuerdo del equipo editorial.

## Agente de IA (periodista / redes / dev)

- **Periodista:** produce expedientes estructurados; no publica.
- **Redes:** genera bajadas y hashtags sobre versión aprobada.
- **Dev:** mantiene código y documentación; no publica contenido editorial.

Ver [../agents/README.md](../agents/README.md) y [../07-ia/orquestacion-agentes.md](../07-ia/orquestacion-agentes.md).

## Casos de uso principales

| Caso | Actor | Resultado |
| --- | --- | --- |
| Explorar temas en seguimiento | Lector | Portada con filtros en sección acotada |
| Buscar un programa | Lector | Catálogo `/casos` con `q` y filtros |
| Leer ficha completa | Lector | `/casos/{slug}` con cronología y fuentes |
| Comparar presupuesto o cobertura | Lector | `/comparador` con URL compartible |
| Investigar y proponer borrador | Editor / agente | API/MCP → `draft` |
| Revisar y publicar | Revisor | Estado `published`, sitemap y OG actualizados |
| Corregir error material | Revisor | Historial de correcciones + invalidar redes/OG |
| Aportar fuente | Lector | Formulario; no publicación directa |
