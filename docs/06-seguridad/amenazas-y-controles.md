# Amenazas y controles

> Basado en plan v1.0 §12.

## Aplicación

| Amenaza | Control |
| --- | --- |
| XSS en fichas | Sanitizar HTML; CSP; no scripts arbitrarios en campos editoriales |
| SSRF al fetch de URLs de aportes | Allowlist de esquemas, bloquear IPs privadas, timeout, sin redirects abiertos |
| Fuga de borradores | API pública solo `published`; auth en admin; `noindex` en preview |
| Token robado | Scopes mínimos, rotación, rate limit, auditoría |
| Spam en formularios | Rate limit, honeypot, moderación humana |
| Dependencias vulnerables | `composer audit`, `npm audit`, actualizaciones programadas |
| Secretos en git | `.gitignore`, revisión pre-push, ver [../09-open-source/contribuir.md](../09-open-source/contribuir.md) |

## Infraestructura

- HTTPS, headers de seguridad (HSTS, X-Frame-Options, etc.)
- Backups cifrados en tránsito; prueba de restore
- Entorno de prueba separado de producción

## MCP / API

- Autenticación en ruta MCP web
- Desactivar MCP si no se usa (`MCP_ENABLED=false`)
- No loguear cuerpos con PII de aportes

## Respuesta a incidentes

Ver [../09-open-source/seguridad-reportes.md](../09-open-source/seguridad-reportes.md).
