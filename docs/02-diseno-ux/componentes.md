# Componentes UI

Superficie vigente (ver [DESIGN.md](../../DESIGN.md)). Reemplaza la lista de [diseno-claro-v3.md](diseno-claro-v3.md) §11 y §19.

| Componente | Responsabilidad |
| --- | --- |
| `shell/AppShell` | Layout: rail, header con buscador, menús Estado/Tipo en portada, footer |
| `shell/IconRail` | Navegación principal y filtro de categorías (multiselección en la portada) |
| `shell/FilterMenu` | Menú desplegable multiselección para Estado y Tipo |
| `content/StatusBadge` | Estado público con texto e ícono propio |
| `content/SocialReel` | Reacciones en redes, carrusel vertical con Anterior/Siguiente |
| `utils/topicLabels` | Rótulos en español para etapa, evidencia, efecto, tipo y fechas |
| Plantilla `.doc` (CSS) | Páginas internas: encabezado, secciones, definiciones, pasos, tablas |

Pendientes cuando haya datos reales: fuentes documentales por tema, cronología, historial de correcciones, pieza de patrocinio rotulada (máximo una por página, después de la comparación).

Los componentes **no** son fuente de verdad editorial; leen datos estructurados del API.
