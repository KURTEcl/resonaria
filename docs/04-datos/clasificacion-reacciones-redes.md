# Clasificación de reacciones en redes

Cada pieza curada en la ficha (`reactions`) lleva **`author_kind`**: el tipo de cuenta o voz, independiente de la plataforma (Instagram, TikTok, X).

| Valor | Uso editorial |
| --- | --- |
| `politico` | Autoridades, candidatos, parlamentarios, voceros de partido |
| `periodista` | Reporteros, columnistas, medios personales de prensa |
| `persona_natural` | Ciudadanía, testimonios, cuentas personales sin rol institucional |
| `medio_oficial` | Ministerios, servicios públicos, cuentas institucionales del Estado |
| `medio_comunicacion` | Diarios, radios, TV, portales informativos |
| `organizacion` | ONG, gremios, movimientos, colectivos |
| `sin_clasificar` | Pendiente de revisión (evitar dejar en público) |

La etiqueta se define al agregar la reacción (MCP `add_reaction`, campo `author_kind`). En la ficha el lector puede filtrar por tipo de voz además de por red.

Configuración canónica de etiquetas: `apps/api/config/reaction_author_kinds.php`.
