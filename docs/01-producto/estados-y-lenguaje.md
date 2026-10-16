# Estados y lenguaje público

> Derivado del plan v1.0 §6 paso 4 y diseño v3 (etiquetas simplificadas en UI).

## Campos separados en datos

1. **Estado de la decisión** (procedimental).
2. **Disponibilidad/calidad de evidencia**.
3. **Efectos observados o estimados**.
4. **Estado editorial de publicación** (`draft`, `in_review`, `published`).

La interfaz no debe convertir “publicado” en “verificado”, ni “aprobado” en “pérdida ya aplicada”.

## Estados procedimentales (precisos)

| Estado de la medida | Uso |
| --- | --- |
| Anunciada | Existe anuncio atribuible; falta acto o texto formal |
| Propuesta formal | Existe texto o instrumento sometido a decisión |
| En tramitación | El texto se discute y puede cambiar |
| Aprobada, pendiente de vigencia | Aprobación documentada; faltan hitos para su aplicación |
| Vigente | Se cumplen los hitos de entrada en vigor del instrumento |
| En aplicación | Existe evidencia de ejecución o efectos operativos |
| Suspendida / retirada / rechazada | El curso de la medida se interrumpe; explicar por qué |
| Revertida / sustituida | Otra decisión modifica su resultado; indicar alcance |

## Etiquetas simplificadas en catálogo (UI v3)

Mapeo editorial (ejemplo; cada caso define su etiqueta desde el estado preciso):

| Etiqueta UI | Origen típico |
| --- | --- |
| En discusión | Anunciada, propuesta formal, en tramitación |
| Aprobado | Aprobada pendiente de vigencia o vigente sin efecto operativo documentado |
| En aplicación | En aplicación |
| Revertido | Revertida / sustituida |

La etiqueta “En discusión” **no** se deduce de palabras en el título.

## Lenguaje en títulos, OG y redes

- Usar **condicional** si la medida no está consumada: “en riesgo”, “propuesta”, “contempla”, no “eliminado” sin acto.
- Diferenciar **presupuesto propuesto** de **ley aprobada** y de **ejecución**.
- Incluir límites en resúmenes: “según proyecto de ley…”, “por verificar”.
- El titular nombra el **programa, derecho o servicio**. No uses como fórmula repetida «Presupuesto 2027», «Ley de Presupuestos» u otro vehículo compartido: varios casos del mismo paquete se vuelven indistinguibles. El origen común va en el gancho o el cuerpo.

Ejemplos: mal «Presupuesto 2027 deja sin financiamiento Yo Elijo Mi PC»; bien «Yo elijo mi PC queda sin fondos en la propuesta para 2027».

## Vocabulario ambiguo o de doble sentido

En titulares, comparaciones, hitos y bajadas de redes, evita palabras que en Chile suelen leerse con doble sentido o que distraen del hecho. Si la idea es meteorológica o de intensidad, usa formulaciones neutras.

| Evitar en copy público | Preferir |
| --- | --- |
| pico del evento / antes del pico | **situación previa**, intensificación, momento de mayor impacto, hora de mayor registro |
| (otros casos puntuales) | Releer en voz alta; si suena a jerga sexual o a insulto coloquial, reformular |

La interfaz de **noticias en desarrollo** ya usa «Situación previa» y «Lo reportado en el episodio» en el bloque comparativo; no reintroduzcas «pico» en `before_text`, `after_text` ni en títulos de hitos.

## Noticia en desarrollo vs seguimiento de decisión

| | Seguimiento de decisión (ley, presupuesto, trámite) | Noticia en desarrollo (clima, emergencia, hecho en curso) |
| --- | --- | --- |
| Señales en datos | `change_types`, estado procedimental distinto de solo trámite, evidencia o efecto documentado | Hitos (`add_update`) **sin** esos campos de decisión |
| Bloque comparativo (UI) | «Antes y con el cambio» · «Hasta ahora» / «Si se aprueba así» | «Antes y durante el episodio» · **«Situación previa»** / **«Lo reportado en el episodio»** |
| Línea de tiempo (UI) | «Cómo va»; flechas riesgo / reversión / incierto | **«Evolución»**; fecha y hora (`occurred_at` cuando el mismo día importa); sin retórica de trámite legislativo |
| Redacción de `before_text` / `after_text` | Línea base frente a propuesta o escenario si se aprueba | Condiciones **antes** del episodio frente a lo que reportan medios o autoridades **durante** el episodio |
| Hitos con `add_update` | `direction`: `riesgo`, `reversion` o `incierto` según el rumbo del proyecto | Etiquetas factuales (alerta, corte de luz, declaración); `occurred_at` en ISO cuando hay hora; no forzar lenguaje de «propuesta negativa» |

No mezcles el lenguaje de presupuesto o ley en una cobertura meteorológica: el lector debe ver una cronología de hechos, no «si se aprueba así».

Ver también [seo-urls-y-compartir.md](seo-urls-y-compartir.md), [protocolo-de-caso.md](../03-metodologia/protocolo-de-caso.md) y [periodista-investigador.md](../agents/periodista-investigador.md).
