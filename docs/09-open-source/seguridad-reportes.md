# Reportes de seguridad

Ver también [SECURITY.md](../../SECURITY.md).

## Qué reportar

- Autenticación rota, escalada de privilegios, IDOR en API.
- SSRF, XSS almacenado, inyección SQL.
- Exposición de borradores o datos de aportes.
- Configuración de despliegue documentada que induce a secretos públicos.

## Qué no es vulnerabilidad del código

- Contenido editorial incorrecto (usar canal de correcciones del sitio).
- Phishing de dominios similares (reportar a registrador/host).

## Proceso

1. Reporte privado al mantenedor.
2. Confirmación y ventana de corrección acordada.
3. Release parcheado y nota de seguridad si aplica.
4. Crédito al reportero si lo desea.

## Rotación tras filtración

- Revocar tokens Sanctum/MCP afectados.
- Rotar `APP_KEY` solo con plan de re-cifrado de sesiones.
- Notificar operadores de instancias self-host en caso de vulnerabilidad crítica.
