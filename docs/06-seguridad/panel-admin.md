# Panel de administración

## Roles

| Rol | Permisos |
| --- | --- |
| `lector` | Ver borradores y publicados |
| `editor` | Crear/editar borradores, enviar a revisión |
| `revisor` | Aprobar, publicar, solicitar cambios |
| `admin` | Usuarios, tokens, configuración |

## Token `agente_ia`

- Scopes: `content:read`, `content:write_draft` (por defecto).
- **Sin** `content:publish`.
- Registro de actor: `agent_token_id` + etiqueta de cliente (`cursor`, `claude`, etc.).

## Autenticación humana

- Contraseña fuerte + **2FA obligatorio** para roles que publican.
- Sesiones con timeout; rotación de sesión al cambiar contraseña.

## Panel mínimo (MVP)

- Cola `in_review`
- Diff entre versiones
- Aprobar / rechazar con comentario
- Historial de auditoría (quién publicó, cuándo)

Operación diaria puede hacerse desde agentes + este panel de revisión.

## Publicación

- Confirmación explícita; no un solo clic accidental desde borrador IA.
- `publish` registra `user_id` del revisor.

## Correcciones

- Cambios materiales incrementan versión e invalidan OG/redes dependientes.

Ver [flujo-editorial.md](../05-arquitectura/flujo-editorial.md).
