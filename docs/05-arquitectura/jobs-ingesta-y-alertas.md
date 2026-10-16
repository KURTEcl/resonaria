# Jobs, ingesta y alertas

## Colas (Redis)

- `default`: tareas rápidas
- `ingest`: descarga/validación de URLs (con límites SSRF)
- `notifications`: correo interno de aportes (futuro)

## Post-MVP: alertas editoriales

- Monitoreo de fuentes (RSS/API cuando existan) → candidatos de investigación, **no** publicación automática.
- Deduplicación por URL y hash de documento.

## Límites

- Timeout y reintentos con backoff.
- Presupuesto de costo para integraciones de pago (X API, etc.).
- Feature flags para apagar jobs sin desplegar código nuevo.

## Registro

- Log de job id, caso relacionado, resultado; sin PII innecesaria.

Ver plan v1.0 §10 (posterior al piloto) y [../07-ia/politica-de-uso.md](../07-ia/politica-de-uso.md).
