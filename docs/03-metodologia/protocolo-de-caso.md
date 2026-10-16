# Protocolo De Caso

> Derivado de [QuePerdimos-plan-de-proyecto.md](../../QuePerdimos-plan-de-proyecto.md) v1.0.

## 6. Protocolo para evaluar y publicar un caso

### Paso 1 — Identificar la unidad de análisis

Determinar si se evalúa una norma, un programa, un componente, un convenio o una institución. Crear relaciones entre ellos para no contar varias veces el mismo cambio.

### Paso 2 — Acreditar la situación anterior

Registrar período, requisitos, cobertura, prestaciones, recursos y origen. Distinguir creación inicial de ampliaciones posteriores: el crédito histórico puede involucrar varias administraciones y decisiones legislativas.

### Paso 3 — Localizar la decisión

Guardar autor, organismo, tipo de acto, fecha, documento y localizador. En proyectos de ley: boletín, versión e indicación pertinente. Una declaración de prensa puede ser un anuncio, no el acto que materializa el cambio.

### Paso 4 — Establecer el estado

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

El estado procedimental, la disponibilidad de evidencia y el efecto observado se guardan en campos separados. Una medida vigente puede tener impacto todavía no cuantificado.

### Paso 5 — Comparar antes y después

Determinar si existe eliminación, recorte, reducción de cobertura, barrera de acceso, interrupción, transferencia o continuidad parcial.

En **noticias en desarrollo** (hecho en curso, sin expediente de ley o presupuesto), la comparación no es «hasta ahora / si se aprueba así»: es **situación previa** frente a **lo reportado durante el episodio**. Evita palabras con doble sentido en Chile (p. ej. «pico»); ver [estados-y-lenguaje.md](../01-producto/estados-y-lenguaje.md).

Para presupuestos:

- Usar la misma unidad, año base y perímetro del programa.
- Indicar si la comparación es entre leyes aprobadas, presupuesto vigente, ejecución o propuesta.
- Distinguir moneda corriente de valores ajustados por inflación y documentar el deflactor usado.
- Revisar si recursos reaparecen en otra asignación o institución.
- No sumar total y componentes ni distintos años como una única pérdida.

Fórmula de variación porcentual: `(monto nuevo / monto base - 1) × 100`. Si el monto base es cero, no calcular porcentaje. La fórmula no determina por sí sola una pérdida de prestaciones.

### Paso 6 — Evaluar población y causalidad

Separar beneficiarios del año anterior, población potencial y personas afectadas comprobadas. No asumir que son iguales ni sumarlas entre fichas sin resolver duplicaciones.

Documentar si el efecto deriva directamente de la decisión, está condicionado a una aprobación futura o requiere otra evidencia. Explicitar supuestos de cualquier estimación.

### Paso 7 — Examinar argumentos y alternativas

Registrar justificación oficial, evaluación citada, fecha y alcance. Revisar si la evidencia recomienda eliminar, rediseñar o corregir problemas. Identificar sustitutos y comparar presupuesto, requisitos, cobertura, prestaciones y calendario; una promesa de reemplazo debe figurar como promesa hasta que haya respaldo suficiente.

### Paso 8 — Atribuir responsabilidades con precisión

Separar propuesta del Ejecutivo, indicaciones parlamentarias, votación, acto administrativo y ejecución. Verificar qué se votó exactamente: una votación general de una partida no equivale necesariamente a votar de manera aislada el programa descrito.

### Paso 9 — Redactar y revisar

Cada afirmación central tendrá respaldo primario cuando esté disponible. Si solo existe respaldo periodístico, atribuirlo al medio y declarar que el documento no se ha contrastado. Las cifras complejas requieren revisión del cálculo. Una ficha de alto impacto debe tener segunda revisión; si no hay revisor disponible, reducir sus conclusiones al material comprobado y dejar visible la limitación.

**Devolver a revisión (no publicar):** ficha en prosa plana, sin negritas ni marcas de color; titular que repite «Presupuesto 2027» u otra fórmula compartida en vez de nombrar el programa, derecho o servicio afectado. Ver [periodista-investigador.md](../agents/periodista-investigador.md).

#### Titular

El titular identifica el afectado, no el vehículo legal común. Si varios casos salen del mismo proyecto de ley, cada uno se titula por el programa o el derecho, no por «Presupuesto 2027». Esa procedencia se explica en el gancho o en `detail`. Revisar títulos ya publicados antes de fijar uno nuevo.

#### Formato visual obligatorio de la ficha

El contenido debe entregarse listo para la lectura visual del portal, no como texto plano. La sintaxis admitida es:

| Marca | Presentación | Uso editorial |
| --- | --- | --- |
| `**texto**` | Negrita | Estado del trámite, límite de la evidencia o conclusión clave. |
| `{-texto}` | Rojo de pérdida | Rebaja, eliminación, menor cobertura o cifra negativa comprobada/propuesta. |
| `{+texto}` | Verde de continuidad o aumento | Línea base, recurso conservado, aumento o reversión favorable. |
| `[n]` | Referencia numerada | Enlace a la fuente estructurada que ocupa la posición `n`. |
| `[[slug\|texto]]` | Enlace interno | Otra nota publicada del portal. El `slug` sale de `list_topics`. Ver [enlaces-internos.md](enlaces-internos.md). |
| `## Título` | Sección desplegable | Un asunto por bloque dentro de `detail`. |

Reglas de aplicación:

- El `hook`, `what_changes`, `who_affected`, `before_text`, `after_text` y `detail` deben llevar marcas cuando hay cifra, estado o límite. Entregarlos en prosa plana es contenido incompleto y se reescribe.
- El signo `$` dentro de `{-…}` o `{+…}` solo abre una cifra en pesos (`{-$1.896 millones}`). Delante de una palabra se publica literal (`$menos`) y no corresponde.
- Usar color solo cuando exprese significado: rojo para pérdida/riesgo y verde para aumento, continuidad o restitución. Nunca usarlo como decoración ni como único medio para explicar el sentido.
- Mantener la negrita selectiva: una o dos ideas clave por párrafo, no párrafos completos.
- Abrir `detail` con el estado del trámite y continuar con magnitud del cambio, respuesta oficial, población afectada y pendientes de verificación.
- Registrar las fuentes en `sources`; no pegar una bibliografía Markdown dentro de `detail`. Las referencias `[n]` deben corresponder al orden real de esas fuentes.
- Si otra nota del portal da contexto o es consecuencia, enlazarla en el cuerpo con `[[slug|texto visible]]` (y `add_link` si hace falta fijar el tipo de vínculo). No uses `[texto](url)` ni la URL de Resonaria. Cómo hacerlo: [enlaces-internos.md](enlaces-internos.md).
- Antes de publicar, previsualizar la ficha y comprobar que no queden visibles marcas sin interpretar, enlaces Markdown literales ni párrafos extensos sin jerarquía.

### Paso 10 — Programar seguimiento

Registrar próximo hito, fecha de revisión y responsable. Si pasa la fecha sin revisión, mostrar “Actualización pendiente” y excluir el caso de resúmenes que afirmen representar la situación actual.

En cada revisión, buscar prensa y redes posteriores a la publicación y adjuntar lo que aún no esté en `sources` o `reactions`. Un enlace Markdown dentro de `detail` no sustituye una fuente estructurada.

En cobertura de hechos en curso, registrar hitos con fecha y, si aplica, hora (`occurred_at`); la línea de tiempo del portal muestra «Evolución», no el rumbo legislativo de «Cómo va».

**Bloqueos de publicación:** ausencia de una situación anterior identificable; acusación sin respaldo; número sin origen; atribución dudosa; estado contradictorio. No requieren bloquear una ficha limitada que exponga claramente lo que sí se conoce.
