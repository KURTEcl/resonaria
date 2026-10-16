# Alcance editorial (ejemplo de instancia)

> **Open source:** la biblia de tu portal vive en `portal/bible/editorial-scope.md`. Plantilla genérica: [alcance-editorial-plantilla.md](alcance-editorial-plantilla.md).

El texto siguiente describe **un despliegue de referencia** (actualidad con contexto y seguimiento). No es la configuración por defecto del software.

## Tipos de contenido

| Tipo | Propósito | Tratamiento |
| --- | --- | --- |
| Noticia | Informar un hecho reciente de interés público | Titular factual, fecha, autoría, atribución y fuentes; separar lo confirmado de lo que sigue en desarrollo |
| Análisis | Explicar antecedentes, actores, argumentos e implicancias | Distinguir evidencia de interpretación y señalar límites o incertidumbres |
| Opinión / columna | Presentar una perspectiva argumentada | Firma visible, rótulo de opinión y argumentos atribuibles; no presentarla como información neutral |
| Seguimiento | Documentar la evolución de una decisión pública y sus efectos | Mantener estados, cronología, fuentes, evidencia y correcciones del modelo actual |

Las noticias pueden enlazar a análisis, columnas y seguimientos relacionados. En el cuerpo se escribe `[[slug|texto visible]]`; la tool `add_link` registra el vínculo. Cómo hacerlo: [../03-metodologia/enlaces-internos.md](../03-metodologia/enlaces-internos.md). Una pieza puede pertenecer a un país o ámbito geográfico, además de uno o más temas.

## Organización del sitio

- Portada con jerarquía editorial y módulos separados para Chile, Mundo, Análisis y Opinión.
- Flujos de actualidad con filtros por cobertura geográfica, tema y tipo de contenido.
- Páginas de artículo con titular, bajada, firma, fecha, tipo, cuerpo y fuentes.
- Seguimientos de decisiones públicas como sección especializada, con sus páginas y metodología propias.
- Búsqueda transversal que encuentre noticias, análisis, columnas y seguimientos.

## Principios editoriales

- Mostrar fecha, autoría y tipo de pieza de forma clara.
- En noticias, atribuir afirmaciones y enlazar fuentes; actualizar o corregir la pieza cuando cambien los hechos.
- En análisis y columnas, distinguir hechos citados, inferencias y postura del autor.
- En cobertura internacional, identificar el país o ámbito y explicar por qué el tema es relevante para la audiencia.
- No convertir tendencias o publicaciones en redes sociales en evidencia por sí solas.
- No abrir comentarios públicos de lectores en esta etapa. El debate político se publica como análisis o columna firmada y pasa por el proceso editorial.

## Implicancias para el producto

El modelo actual está centrado en expedientes de decisiones públicas. Para sostener Resonaria, el producto deberá incorporar artículos como una entidad editorial propia, con tipo, firma, sección, geografía, fecha de publicación, fuentes, estado editorial y correcciones. Los expedientes existentes se conservan como seguimientos especializados; no deben forzarse dentro de un esquema de noticias.

## Pendiente de definición

- Frecuencia y capacidad de publicación.
- Política de firmas invitadas y revisión de columnas.
- Secciones temáticas y cobertura internacional prioritaria.
- Sistema gráfico editorial y variantes definitivas de marca.
- Política de archivo, actualización y correcciones para artículos.
