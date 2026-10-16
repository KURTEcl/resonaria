# Documentación — Resonaria (software)

Documentación del **portal de código abierto** Resonaria: Laravel (API + MCP) + Nuxt (SSR). Cada instalación define su propia misión editorial en `portal/bible/` (generada con `./scripts/install-wizard.sh`).

**Agentes:** [agents/](agents/) · **Instalación para LLM:** [../AGENTS.md](../AGENTS.md)

English readme: [../README.md](../README.md) · Español: [../README.es.md](../README.es.md)

## Índice

### Casos y material editorial

- [Índice de expedientes y convención de carpetas](casos/README.md)
- [Inventario transversal de Presupuesto 2027](casos/presupuesto-2027-inventario.md)

### 00 — Visión

- [proposito-y-alcance.md](00-vision/proposito-y-alcance.md)
- [principios-editoriales.md](00-vision/principios-editoriales.md)
- [fuera-de-alcance.md](00-vision/fuera-de-alcance.md)

### 01 — Producto

- [alcance-editorial-resonaria.md](01-producto/alcance-editorial-resonaria.md)
- [personas-y-casos-de-uso.md](01-producto/personas-y-casos-de-uso.md)
- [mapa-de-paginas.md](01-producto/mapa-de-paginas.md)
- [ficha-publica.md](01-producto/ficha-publica.md)
- [estados-y-lenguaje.md](01-producto/estados-y-lenguaje.md)
- [seo-urls-y-compartir.md](01-producto/seo-urls-y-compartir.md)
- [roadmap-por-etapas.md](01-producto/roadmap-por-etapas.md)

### 02 — Diseño UX

- [identidad-visual.md](02-diseno-ux/identidad-visual.md) — marca aprobada, recursos y reglas de uso

- [diseno-claro-v3.md](02-diseno-ux/diseno-claro-v3.md) — especificación completa
- [tokens-y-tipografia.md](02-diseno-ux/tokens-y-tipografia.md)
- [referencia-navegacion-tcghub.md](02-diseno-ux/referencia-navegacion-tcghub.md)
- [referencia-decide-chile.md](02-diseno-ux/referencia-decide-chile.md)
- [rutas-y-filtros.md](02-diseno-ux/rutas-y-filtros.md)
- [comparador.md](02-diseno-ux/comparador.md)
- [componentes.md](02-diseno-ux/componentes.md)
- [accesibilidad-y-estados-ui.md](02-diseno-ux/accesibilidad-y-estados-ui.md)

### 03 — Metodología

- [jerarquia-de-evidencia.md](03-metodologia/jerarquia-de-evidencia.md)
- [protocolo-de-caso.md](03-metodologia/protocolo-de-caso.md)
- [enlaces-internos.md](03-metodologia/enlaces-internos.md) — cómo asociar notas del portal (`[[slug|texto]]` y `add_link`)
- [directorio-de-fuentes.md](03-metodologia/directorio-de-fuentes.md)
- [correcciones-y-transparencia.md](03-metodologia/correcciones-y-transparencia.md)

### 04 — Datos

- [modelo-conceptual.md](04-datos/modelo-conceptual.md)
- [reglas-de-calidad.md](04-datos/reglas-de-calidad.md)
- [exportacion-y-reutilizacion.md](04-datos/exportacion-y-reutilizacion.md)

### 05 — Arquitectura

- [overview-laravel-nuxt.md](05-arquitectura/overview-laravel-nuxt.md)
- [contratos-api.md](05-arquitectura/contratos-api.md)
- [integracion-mcp.md](05-arquitectura/integracion-mcp.md)
- [flujo-editorial.md](05-arquitectura/flujo-editorial.md)
- [busqueda-y-filtros.md](05-arquitectura/busqueda-y-filtros.md)
- [descubrimiento-web.md](05-arquitectura/descubrimiento-web.md)
- [jobs-ingesta-y-alertas.md](05-arquitectura/jobs-ingesta-y-alertas.md)
- [self-host.md](05-arquitectura/self-host.md)

### 06 — Seguridad

- [panel-admin.md](06-seguridad/panel-admin.md)
- [amenazas-y-controles.md](06-seguridad/amenazas-y-controles.md)
- [privacidad-y-datos.md](06-seguridad/privacidad-y-datos.md)

### 07 — IA

- [mcp-overview.md](07-ia/mcp-overview.md) · [mcp-cursor.md](07-ia/mcp-cursor.md) · [mcp-codex.md](07-ia/mcp-codex.md) · [mcp-production.md](07-ia/mcp-production.md)
- [skills-install.md](07-ia/skills-install.md)
- [Evaluación de skills externos (redes y motion)](07-ia/skills-externos-evaluacion.md)

- [politica-de-uso.md](07-ia/politica-de-uso.md)
- [orquestacion-agentes.md](07-ia/orquestacion-agentes.md)
- [trazabilidad-y-costos.md](07-ia/trazabilidad-y-costos.md)
- [integracion-tecnica-futura.md](07-ia/integracion-tecnica-futura.md)
- [base-publicaciones-redes.md](07-ia/base-publicaciones-redes.md)

### 08 — Operaciones

- [deploy.md](08-operaciones/deploy.md)
- [rutina-editorial.md](08-operaciones/rutina-editorial.md)
- [metricas.md](08-operaciones/metricas.md)
- [financiamiento-y-publicidad.md](08-operaciones/financiamiento-y-publicidad.md)
- [checklist-lanzamiento.md](08-operaciones/checklist-lanzamiento.md)

### 09 — Open source

- [gobernanza.md](09-open-source/gobernanza.md)
- [publicar-repositorio.md](09-open-source/publicar-repositorio.md)
- [contribuir.md](09-open-source/contribuir.md)
- [codigo-de-conducta.md](09-open-source/codigo-de-conducta.md)
- [seguridad-reportes.md](09-open-source/seguridad-reportes.md)

### Agentes (portables)

- [agents/README.md](agents/README.md)
- [agents/dev-portal.md](agents/dev-portal.md)
- [agents/periodista-investigador.md](agents/periodista-investigador.md)
- [agents/redes-contenido.md](agents/redes-contenido.md)

## Convenciones

- Documentación en **español**; código e identificadores en **inglés** cuando corresponda.
- Los cambios editoriales de fondo se reflejan en metodología y en agentes; la API y el diseño deben mantenerse alineados.
- Ante conflicto entre mockup y tokens, prevalecen los **tokens** del diseño v3.

## Instalación

```bash
./scripts/install-wizard.sh
./dev-start.sh
```

Ver [05-arquitectura/self-host.md](05-arquitectura/self-host.md) y [08-operaciones/deploy.md](08-operaciones/deploy.md).
