# Enlaces internos entre notas

En el portal, una nota puede apuntar a otra nota ya publicada —una noticia a un seguimiento, un recorte presupuestario a sus efectos— como Wikipedia: el lector hace clic en el texto y abre la ficha relacionada.

No uses un enlace Markdown `[texto](https://…)` ni una URL del sitio. Eso se ve como texto crudo o sale del portal.

## En el contenido del tema

En `hook`, `what_changes`, `who_affected`, `before_text`, `after_text` o `detail` escribe:

```text
[[slug-de-la-otra-nota|texto que ve el lector]]
```

El `slug` es el de la ficha destino (`list_topics` / `get_topic`). El texto a la derecha del `|` es lo que se lee en la frase. Si omites el `|`, el portal muestra el título de esa nota.

| Mal | Bien |
| --- | --- |
| El recorte a estudios de suelo agrava el riesgo del temporal. | El [[estudios-de-suelo-presupuesto-2027\|recorte a estudios de suelo]] agrava el riesgo del temporal. |
| Ver también: /casos/ley-cholito-recorte | El recorte se suma a lo ya documentado en [[ley-cholito-proponen-reducir-en-86-los-recursos-para-tenencia-responsable\|Ley Cholito]]. |
| `[nota](https://resonaria.cl/casos/otro-slug)` | `[[otro-slug\|nota]]` |

Una o dos menciones por pieza bastan. El enlace va dentro de la frase, no como bibliografía al final.

Al publicar o al llamar `update_topic` con ese markup, el portal crea la asociación y la muestra en **Notas relacionadas**. En la nota destino aparece **Aparece también en**.

## Con la tool `add_link`

Cuando la relación importa aunque no quepa en un párrafo, o cuando quieres fijar el tipo de vínculo:

1. `list_topics` y anotar el slug destino (tiene que estar publicado).
2. `add_link`:
   - `slug`: la nota que apunta
   - `to_slug`: la nota destino
   - `relation`: `contexto`, `consecuencia`, `antecedente` o `relacionado`
   - `note`: por qué se asocian, en una frase
   - `anchor`: texto visible (si omites, usa el título destino)
3. Pegar el `markup` que devuelve la tool en `hook` o `detail` con `update_topic`.

| `relation` | Cuándo |
| --- | --- |
| `contexto` | La otra nota explica o encuadra esta |
| `consecuencia` | La otra nota es un efecto o un hecho posterior |
| `antecedente` | La otra nota ocurrió antes y hace falta para entender esta |
| `relacionado` | Hay vínculo editorial, sin un orden claro |

No asocies una nota consigo misma. No inventes slugs. Si la URL ya es una fuente externa, eso es `add_source`, no `add_link`.

## Ejemplo

Una noticia sobre un temporal y un seguimiento que documenta el recorte a estudios de suelo:

```text
hook: Las lluvias encontraron cauces sin estudio reciente. El [[estudios-de-suelo-presupuesto-2027|recorte a estudios de suelo]] agrava el riesgo. **No es ley**.
```

```text
add_link
  slug: temporal-zona-centro
  to_slug: estudios-de-suelo-presupuesto-2027
  relation: contexto
  note: El recorte a estudios de suelo agrava el riesgo del temporal.
  anchor: recorte a estudios de suelo
```

La noticia apunta al seguimiento (`contexto`). Quien abre el seguimiento ve la noticia en **Aparece también en**.

## Qué no hacer

- Dejar la relación solo en prosa («como se explica en otra ficha») sin `[[slug|texto]]` ni `add_link`.
- Pegar la URL pública de Resonaria dentro del cuerpo.
- Enlazar un documento DIPRES, una ley o una nota de prensa: eso va en `add_source`.
- Duplicar el mismo par de notas. Si ya están asociadas, no llames `add_link` otra vez con los mismos valores.
