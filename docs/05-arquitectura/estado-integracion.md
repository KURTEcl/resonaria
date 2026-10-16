# Estado de la integración (técnica + UI)

Actualizado con el avance del monorepo `apps/api` + `apps/web`.

## Hecho

| Área | Estado |
| --- | --- |
| Laravel API pública | `health`, `cases`, `cases/{slug}` |
| MySQL | Tabla `cases`, seed demo |
| Nuxt SSR | Consume API, rutas `/`, `/casos`, `/casos/[slug]` |
| Dev local | `dev-start.sh`, puertos 8017/3017, `bootstrap-mysql.sh` |
| Identidad visual | Logo SVG en encabezado/pie, isotipo en rail, favicon SVG/ICO y Apple touch icon |
| UI diseño v3 | Shell responsive, portada, ilustración SVG, tarjetas, herramientas, apoyo y pie de página |
| Comparador demo | `/comparador`, presupuesto/cobertura ficticios, selección de programa, tabla, CSV y enlace compartible |
| Contenido destacado demo | Pestañas documentos/noticias/reacciones, navegación por teclado |
| SEO público | sitemap.xml, robots.txt, llms.txt, canonical y JSON-LD |

## Pendiente (integración completa)

| Área | Prioridad |
| --- | --- |
| Panel admin (borrador → revisión → publicar) | Alta |
| Modelos: fuentes, afirmaciones, hitos, correcciones | Alta |
| Comparador con API y datos documentados | Media |
| Entidades reales para documentos / noticias / reacciones | Media |
| Gráficos (ChartPanel) con datos reales | Media |
| OpenAPI, Sanctum tokens agente, MCP | Media-baja |
| Páginas metodología, correcciones, apoyo operativo | Media |

La **biblia** en `docs/` describe el producto final; este archivo solo rastrea el código en `apps/`.

Los valores del comparador son fixtures de interfaz independientes de los expedientes Laravel. No se reemplazan errores de API por información ficticia. Validación: build Nuxt y respuestas HTTP 200 de portada y comparador; revisión visual en navegador pendiente.
