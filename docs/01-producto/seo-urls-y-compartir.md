# SEO, URLs amigables y compartir

Reglas de **producto y editorial** para enlaces y previews. Implementación técnica en [../05-arquitectura/descubrimiento-web.md](../05-arquitectura/descubrimiento-web.md).

## URLs

| Ruta | Patrón |
| --- | --- |
| Portada | `/` |
| Catálogo | `/casos?area=&estado=&tipo=&q=` |
| Ficha | `/casos/{slug}` |
| Comparador | `/comparador?...` |
| Estáticas | `/metodologia`, `/correcciones`, `/apoyar` |

- Slug estable (se congela al publicar). Un título reescrito no cambia la URL y no exige 301.
- Cambios de slug, si alguna vez hacen falta, van con **301** y `slug_history`.
- Sin barra final; canonical absoluta (`FRONTEND_URL`).
- Listados filtrados: **self-canonical** con query completa (compartir selección).

## Open Graph y Twitter

Metadatos desde **datos estructurados** publicados:

| Campo | Regla |
| --- | --- |
| Título | Condicional si la medida es propuesta; coherente con [estados-y-lenguaje.md](estados-y-lenguaje.md) |
| Descripción | Resumen 80–150 palabras con límites explícitos |
| Imagen | Una imagen editorial por nota, 16:9, compartida por la cabecera y la tarjeta de portada; usar una derivación de esa misma imagen para Open Graph (1200×630) con el motivo principal dentro del área segura. No contradecir el estado |
| URL | Versión publicada canónica |

Tras **corrección material**, regenerar OG y piezas de redes.

## Título HTML

Patrón de título: `{Título corto} | Resonaria`.

## JSON-LD

Solo contenido publicado: `WebSite` + `SearchAction` en portada; `Article` en fichas con fechas y organización autora.

## Lo que no se indexa

Admin, API, previews de borrador (`noindex`). Ver `robots.txt` y [descubrimiento-web.md](../05-arquitectura/descubrimiento-web.md).

## Checklist al publicar una ficha

- [ ] Estado procedimental reflejado en título OG y meta description
- [ ] Slug y canonical correctos
- [ ] `article:modified_time` actualizado
- [ ] La misma imagen se ve bien en cabecera, tarjeta de portada y preview Open Graph; queda aire visual a la izquierda para navegación y titular; el texto sigue siendo HTML y no está incrustado en la imagen
- [ ] Prueba de preview en al menos un canal (WhatsApp o X)
