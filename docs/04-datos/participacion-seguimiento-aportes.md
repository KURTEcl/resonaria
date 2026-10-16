# Participación: seguir, aportar y corregir

## Principio

Sustituyen al like/dislike: miden **interés**, **información nueva** o **calidad de la ficha**, no si el lector “está de acuerdo” con que un cambio sea perjudicial.

## Seguir tema (`topic_follows`)

| Campo | Uso |
| --- | --- |
| `case_id` | Tema seguido |
| `follower_key` | Hash opaco generado en el navegador (localStorage), no email obligatorio |
| `created_at` | Alta |

Índice único `(case_id, follower_key)`. API:

- `POST /api/v1/cases/{slug}/follow` — body opcional `{ "follower_key": "..." }`; si falta, el servidor no crea fila (el cliente debe generar UUID).
- `DELETE /api/v1/cases/{slug}/follow` — misma clave.

Métricas internas: `followers_count` por tema (agregado). Público: solo si en el futuro se muestra “N personas siguen este tema” con umbral alto y sin identificar.

## Aportes

Entidad prevista en el modelo conceptual (`Aporte`). Métricas:

- Aportes recibidos / en revisión / incorporados / descartados
- Tiempo medio hasta primera respuesta editorial

No publicar quién aportó salvo consentimiento explícito.

## Correcciones

Entidad `Corrección` con versión y alcance material. Métricas:

- Correcciones por severidad
- Tiempo hasta publicación de la corrección en ficha

La ficha enlaza a `/correcciones` para reportar errores (flujo editorial, no voto).

## Qué no hacer

- Puntuación ±1 en la ficha
- Ranking “temas más odiados”
- Perfil de lectura por afiliación política inferida
