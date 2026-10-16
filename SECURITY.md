# Seguridad

## Reportar una vulnerabilidad

No abras un issue público con detalles explícitos de explotación.

1. Envía un correo al mantenedor del repositorio (actualizar contacto al publicar el repo).
2. Incluye pasos para reproducir, impacto y versión afectada.
3. Espera confirmación antes de divulgación pública.

Política ampliada: [docs/09-open-source/seguridad-reportes.md](docs/09-open-source/seguridad-reportes.md).

## Alcance

- Código de este repositorio (Laravel, Nuxt, despliegue documentado).
- El contenido editorial de cada instancia desplegada es responsabilidad de quien opera esa instancia.

## Buenas prácticas para operadores

- HTTPS, 2FA en admin, rotación de tokens de agente.
- No commitear `.env` ni claves MCP/IA.
