# Base de publicaciones para Instagram y TikTok

Paquete por tema: una imagen editorial horizontal común a cabecera, tarjeta de portada y preview; un video 9:16 con gráficos y música; un carrusel 4:5 estático; y los textos de bajada. El video no lleva locución. Las cifras salen de la ficha publicada; el gráfico no agrega montos.

Identidad: combinar la textura editorial de expediente (A) con titulares y cifras tipográficas de alto impacto (B). Fondo claro `#f4f6f8` como base, tinta `#12302d`, acento violeta aprobado `#7355C8` solo en marca, petróleo `#0f766e` para el valor nuevo y ámbar con etiqueta para «En discusión». Usar contraste de escala, recortes, marcos, numeración y tramas como recursos; no reducir la pieza a texto alineado y filetes finos. La tipografía actual Schibsted Grotesk local solo llega a peso 600: para titulares display usar una familia de peso alto aprobada antes de integrarla, sin simular negrita con contorno.

## Imagen única de cabecera y portada

Cada nota usa **un solo archivo de imagen**, compartido por la cabecera del artículo, su tarjeta en la portada y los metadatos de compartir. El titular y otros textos se renderizan como texto de la página, no se incrustan en la imagen. Así se mantiene una identidad visual común y legible en distintos tamaños.

- **Formato maestro:** horizontal 16:9, preferentemente 1600×900 px o mayor, en WebP optimizado. Componer para recorte adaptable: dejar el motivo principal dentro del 80 % central y no poner información esencial en los bordes. Mantener una zona de menor contraste donde la interfaz pueda superponer texto, si corresponde.
- **Composición preferida para cabeceras:** reservar aproximadamente el 55 % izquierdo como espacio visual tranquilo y de bajo contraste para navegación, titular y bajada; concentrar la ilustración principal en el 45 % derecho. La textura puede unir ambos lados, pero no poner edificios, granizo, papeles u otros detalles fuertes detrás del titular. No dibujar una división vertical rígida. La referencia aprobada es [la captura de la prueba de cabecera](referencias-imagenes/cabecera-temporal-santiago-prueba.png).
- **Dirección por defecto:** ilustración editorial o collage claramente ilustrado, con la paleta y las texturas del proyecto. No imitar una fotografía periodística ni inventar detalles identificables de daños, lugares o personas. Evitar rostros generados cuando no aportan al tema.
- **Cuándo usar fotografía:** preferir una foto auténtica y directamente pertinente cuando exista autorización de uso y crédito verificable. Conservar la imagen sin alteraciones generativas, especialmente en personas, daños y evidencia. No descargar ni reutilizar publicaciones de redes sin revisar derechos, atribución y contexto.
- **Composición:** una escena sencilla y reconocible; sin collage de varias fuentes que sugiera que hechos distintos ocurrieron juntos. Sin texto, números, logos ni marcas de agua generados. El logo del medio y las etiquetas van en la interfaz.
- **Recortes:** usar el mismo archivo maestro en artículo y tarjeta con `object-fit: cover` y ajustar `object-position` solo si hace falta. Revisar que el espacio despejado izquierdo siga libre en cabecera, portada y miniatura de compartir; comprobar también el recorte en móvil antes de publicar. Para Open Graph se puede generar una derivación técnica del mismo maestro, sin cambiar su contenido ni sumar una segunda imagen editorial.
- **Uso editorial:** una imagen generada es ambientación, no evidencia. No atribuirle hechos, daños o testimonios; las afirmaciones deben sostenerse en la ficha y sus fuentes. Acompañar la imagen con texto alternativo descriptivo y guardar internamente su procedencia y licencia.

Prueba inicial de esta pauta: [ilustración 16:9 del temporal en la RM](../../apps/web/public/images/topics/tormenta-rm-2026-10-13.webp). El archivo prueba la dirección y el formato; no documenta daños reales.

## Formatos

| Pieza | Tamaño | Duración | Audio |
| --- | --- | --- | --- |
| Imagen de cabecera y tarjeta | 16:9, maestro 1600×900 px o mayor | — | — |
| Video (Reels y TikTok) | 1080×1920 | 60–90 s; nunca menos de 60 s | Cama instrumental; locución opcional solo si aporta comprensión |
| Carrusel | 1080×1350 | Estático, 5 láminas | No |

Zona segura del video: 64 px a los lados, 140 px arriba, 220 px abajo. El carrusel usa 64 px de margen.

## Apertura y cierre de marca

Todo video final incluye una intro y un outro diseñados como escenas reutilizables. La marca aparece desde el primer fotograma y permanece visible durante el desarrollo; no se usa una cortina larga que retrase el dato.

| Escena | Duración guía | Elementos | Regla editorial |
| --- | --- | --- | --- |
| Intro | 0–4 s | Sello o isotipo Que Perdimos entra con golpe y desplazamiento corto; estado procedimental en alto contraste; gancho grande antes del segundo 4 | El estado se entiende en el primer segundo. No usar una cortina larga ni una intro genérica separada del tema. |
| Desarrollo | 4 s hasta el cierre | Titular, cifras, explicación, hitos, población afectada y límites de la ficha | Cada beat tiene una idea y tiempo de lectura. Mantener logo en zona segura. |
| Outro | Últimos 5–7 s | Resolución del motivo gráfico hacia el isotipo/logo; descriptor «Qué cambia. A quién afecta.»; dominio corto `queperdimos.cl` | Dejar el dominio quieto y visible al menos 3 s. Cerrar música en los últimos 1,6 s. Nunca dibujar la URL completa ni el slug de un caso en imagen o video. |

Intro y outro pueden adaptarse al tema (por ejemplo, cuaderno o barras), pero conservan marca, estado y descriptor. No se agregan hechos o cifras en estas escenas. El dominio breve `queperdimos.cl` puede aparecer en el cierre; el enlace canónico completo queda en la bajada y metadatos de entrega, nunca en los píxeles. Las secuencias `porcentaje` y `como-va` son beats reutilizables; no son exportables como videos finales por sí solas.

## Motion

Una idea por beat. Entrada 500–700 ms con `power3.out`. Stagger 80–120 ms. Cada beat se sostiene lo bastante para leerlo en voz baja, cerca de 6–10 s. La cifra principal cuenta hasta su valor mientras la barra crece. El logo horizontal va arriba, dentro de la zona segura, en todas las piezas. En las barras pareadas, la de «Antes» entra primero; la de «Con el cambio» crece desde el mismo origen en 500–700 ms; el delta aparece al terminar.

Para dirección de motion en HyperFrames (brief, storyboard, contratos `SCENE_SCHEMA` / `MOTION_PRIMITIVES`, anti-PPT, QA de renders) usar [hyperframes-motion-director](https://github.com/geekjourneyx/hyperframes-motion-director): `npx skills add geekjourneyx/hyperframes-motion-director -g`. El estilo por defecto del skill es cine negro; en Qué Perdimos se documenta el override a fondo claro en el brief.

Se sigue la skill [motion-design de LottieFiles](https://github.com/lottiefiles/motion-design-skill), instalada en `~/.agents/skills/motion-design` para Cursor, Claude Code y Codex. En la práctica:

- **Personalidad sobria.** Curvas suaves (`power2.inOut`), nada elástico. Si el cambio es una pérdida, el movimiento va lento y hacia abajo, 600–1200 ms.
- **Tres capas.** Primaria: la cifra y su barra. Secundaria: etiquetas, filetes y deltas. Ambiente: la trama de puntos que deriva y el objeto que respira. El ambiente no pasa del 20 % de presencia.
- **Arco narrativo.** Anticipación (el objeto aparece), acción (la cifra cuenta), reacción (el objeto cambia de estado), resolución (delta y nota).
- **Salidas** con `power2.in`, 0,4 s. Ningún cambio importante se comunica solo con opacidad.

### Estructura base de 60–70 s

Intro y estado (0–4) → título y gancho (4–12) → monto, barras y delta (12–25) → serie o cobertura (25–37) → qué pasó y hitos (37–49) → a quién afecta y límites (49–57) → outro, URL y descriptor (57–64). Ajustar los tiempos al ritmo de lectura; nunca comprimir para bajar de 60 s.

## Gráficos

La escala es compartida: el valor mayor ocupa el ancho útil. No hay torta ni eje inventado.

La propuesta de composición A+B está en [direccion-visual-a-b.svg](../../videos/redes-base/propuestas/direccion-visual-a-b.svg) (PNG de vista previa en la misma carpeta): A combina expediente y montos comparables; B usa el porcentaje como titular, respaldado por los dos montos. A explica; B detiene el scroll. Se pueden combinar dentro de una sola identidad.

1. **Barras pareadas.** Dos barras horizontales por concepto, con unidad. Sirven para un monto o una cobertura.
2. **Delta.** `((after - before) / before) * 100`, un decimal, con «baja» o «alza». Si los montos son aproximados, el porcentaje derivado también se marca como aproximado o se omite; preferir delta publicado con cálculo verificable. Si la ficha solo da el porcentaje, la barra de partida es 100 % y la nueva es el valor relativo. No se inventa el monto que falta.
3. **Ranking corto.** Hasta cinco filas, en el orden de la ficha. Cada fila lleva etiqueta, barra, monto y delta cuando existen los dos valores. Si solo está el recorte, la barra mide ese recorte y no se muestra un porcentaje.

Sin par de cifras ni porcentaje con base, el beat usa el texto de «qué cambia» y no un gráfico.

## Plantillas de video

1. **El cambio.** Por defecto cuando hay cifras. Estado → título → barras pareadas y delta → ranking si hay más de una partida → a quién afecta y URL.
2. **Un solo porcentaje.** Un concepto y un cambio relativo. La base queda escrita debajo del delta.
3. **Cómo va.** Hitos publicados. Una fila con cifra usa una barra pequeña; el resto queda en texto. Cierra con estado y próxima revisión.

Archivos en [../../videos/redes-base/](../../videos/redes-base/): `index.html` (muestra de «El cambio»), `compositions/porcentaje.html`, `compositions/como-va.html`, `carousel/index.html`.

## Música

La cama va con el tono del tema: piano o cuerdas contenidas, sin percusión festiva ni tensión de thriller. La actual es «Dreamer» de Kevin MacLeod (CC BY 4.0), pensativa y sobria, para recortes de programas sociales. Fade in al comienzo, baja al 70 % mientras la cifra cae a cero y fade out en los últimos 1,6 s. Volumen base 0,32. Si un tema pide otro tono, se cambia la pista y su `LICENSE.md`, y también el crédito en la bajada.

La pista vive en `videos/redes-base/assets/` con `LICENSE.md`: título, autor, licencia, URL y si exige atribución. Solo CC0, o una licencia que permita uso comercial en redes sin registrar la pista. Si pide crédito, va en la bajada. No se usa audio de YouTube ni de TikTok.

## Entrada

Narrativa, desde la ficha publicada: `title`, `hook`, `what_changes`, `who_affected`, `public_status_label`, `reviewed_at`, `next_review_at` y URL `https://queperdimos.cl/casos/{slug}`. La URL completa se conserva en datos de producción y bajada; no se inserta en imagen ni video.

Gráfica, armada solo con cifras de esa ficha:

```json
{
  "unit": "millones de pesos",
  "baseline_label": "Antes",
  "change_label": "Con el cambio",
  "series": [
    { "label": "Programa", "before": 100, "after": 88 }
  ]
}
```

No se mezcla un porcentaje de otro agregado si la ficha advierte que no es el mismo universo. Lenguaje condicional si la medida no está consumada. La primera línea visible es el estado.

## Bajada

Junto al MP4, un JSON con caption de Instagram y descripción de TikTok. Primera línea: estado. Después el gancho, el enlace a la ficha y hashtags evaluados (motivo y riesgo). La bajada no agrega cifras que el video no muestre. El crédito musical va al final solo si la licencia lo pide.

Esquema de plataformas y hashtags: [../agents/redes-contenido.md](../agents/redes-contenido.md).

## Checklist antes de publicar

- [ ] Estado en la primera línea del video y de la bajada
- [ ] Cada monto y cada porcentaje está en la ficha de esa `content_version`
- [ ] El ranking sigue el orden de la ficha y no pasa de cinco filas
- [ ] Solo aparece `queperdimos.cl` en el lienzo; no se ve URL completa ni slug de caso
- [ ] `videos/redes-base/assets/LICENSE.md` corresponde al archivo que suena
- [ ] Hashtags con motivo; ninguno ambiguo o asociado a desinformación

## Muestra

Yo elijo mi PC está en preproducción, todavía sin video: [ficha](../casos/becas-tic-presupuesto-2027/caso.md), [pauta](../casos/becas-tic-presupuesto-2027/pauta-redes.md), [guion](../casos/becas-tic-presupuesto-2027/guion-video.md) y [elementos gráficos](../casos/becas-tic-presupuesto-2027/elementos-graficos.md).

Caso de infancia vulnerable, proyecto de presupuesto 2027 (versión corta anterior): [../../videos/redes-base/muestras/infancia-presupuesto-2027.json](../../videos/redes-base/muestras/infancia-presupuesto-2027.json). El MP4 se renderiza desde `videos/redes-base`. Los otros temas copian esta carpeta, cambian las variables y vuelven a renderizar.
