# Financiamiento Y Publicidad

> Derivado de [QuePerdimos-plan-de-proyecto.md](../../QuePerdimos-plan-de-proyecto.md) v1.0.

## 14. Publicidad discreta y financiamiento

### 14.1 Recomendación principal

Comenzar con **aportes voluntarios y patrocinios directos, claramente rotulados**, mediante una página de apoyo y, para anunciantes, una pieza estática servida desde el propio sitio. Evaluar AdSense después de publicar contenido original suficiente y medir tráfico; no basar el lanzamiento en su aprobación ni en ingresos estimados sin datos.

El patrocinio directo ofrece mayor control de formato y anunciante. Requiere trabajo comercial y no garantiza ventas. La selección de anuncios debe ser tan transparente como la metodología editorial.

### 14.2 Opciones

| Modelo | Ventajas | Limitaciones | Decisión propuesta |
| --- | --- | --- | --- |
| Patrocinio directo | Formato, anunciante y precio controlados; pocos scripts externos | Venta, contratos y renovación manual | Primera opción |
| Aportes voluntarios | No ocupan espacio publicitario; compatibles con acceso abierto | Ingreso variable y comisiones | Complemento opcional |
| AdSense con bloques manuales | Integra demanda publicitaria sin vender cada campaña | Aprobación, políticas, terceros y rendimiento variable | Piloto posterior, con un espacio |
| EthicalAds / Carbon Ads | Modelos orientados a anuncios discretos o contextuales | Redes selectivas; encaje y aceptación no garantizados | No asumirlas como solución disponible para este público |
| Contenido patrocinado | Puede aportar ingreso directo | Riesgo de confundir información y promoción | Excluir del MVP |

Carbon declara enfoque en audiencias de tecnología, diseño y desarrollo. EthicalAds ofrece publicidad contextual y una política de un anuncio propio por página. Son referencias de diseño y modelos comerciales; su elegibilidad para un sitio político chileno en español debe consultarse directamente.

### 14.3 Política de formatos

Reglas propuestas para el sitio:

- Máximo inicial de **un anuncio por página**, sin repeticiones durante el scroll.
- Sin popups, popunders, intersticiales, anuncios flotantes ni videos automáticos.
- Sin banners adheridos a la pantalla ni bloqueo de lectura para pedir aportes.
- Sin cuenta regresiva, falsas notificaciones o anuncios disfrazados de navegación.
- Etiqueta visible “Publicidad” o “Patrocinio”, también con lector de pantalla.
- En fichas, ubicación después de la explicación principal y comparación; nunca entre el título y el estado.
- En portada, después del primer grupo de fichas.
- Pieza adaptable al móvil, reservando espacio para evitar saltos de contenido.
- Si no hay campaña, retirar el bloque; no llenar el espacio con promociones innecesarias.
- Carga no bloqueante y desactivación global sin editar cada ficha.
- Pieza directa estática, optimizada y sin audio. No modificar creatividades de una red de anuncios de forma incompatible con sus condiciones.

**Criterio de aceptación:** en un teléfono, el lector comprende el tema y su estado antes de ver publicidad; ninguna pieza tapa texto, captura el foco o inicia sonido.

### 14.4 AdSense: configuración a evaluar

Google documenta bloques display que se insertan manualmente y controles para formatos de Auto ads. Para este proyecto se propone:

1. Solicitar acceso solo con sitio funcional y contenido propio suficiente; la aceptación es decisión de Google.
2. Usar un bloque manual, en el espacio reservado.
3. Mantener Auto ads desactivado; revisar que no se activen colocaciones o experimentos incompatibles con el diseño.
4. Comprobar formatos reales en móvil antes de ampliar.
5. Revisar controles de categorías y anuncios disponibles, sin prometer un filtro perfecto.
6. Revisar privacidad, consentimiento aplicable, rendimiento y requisitos de implementación del proveedor.
7. Cumplir las políticas de contenido y no incentivar clics con mensajes como “haz clic para apoyarnos”.
8. No seleccionar anuncios ni crear audiencias según afiliación o ideología política.
9. Retirar el piloto si los ingresos no justifican su carga o deteriora la lectura.

Publicar periodismo político y contratar publicidad electoral son actividades diferentes. La presencia de temas políticos no permite garantizar aprobación de AdSense ni concluir automáticamente que el sitio esté prohibido. Evaluar las políticas del proveedor sobre contenido y datos antes de integrar.

### 14.5 Independencia y conflictos de interés

Política propuesta para el MVP:

- Excluir financiamiento publicitario de partidos, candidaturas y autoridades objeto de cobertura.
- Revisar vínculos de anunciantes con instituciones o decisiones investigadas.
- No vender patrocinio exclusivo de una ficha que evalúe al propio anunciante.
- No aceptar cláusulas que condicionen publicaciones, resultados o correcciones.
- Publicar identidad de patrocinadores, período y naturaleza del aporte; definir nivel de detalle financiero antes de vender.
- Separar contacto comercial de la decisión editorial, aunque inicialmente lo gestione la misma persona.
- Mantener derecho contractual a retirar una pieza engañosa o incompatible con la política.
- Medir con agregados; no entregar datos de lectores al patrocinador.

El acuerdo comercial debe cubrir pieza, fechas, posición, precio, forma de pago, métricas disponibles, cancelación y ausencia de garantía de clics o influencia editorial.

### 14.6 Cálculo de sostenibilidad

Los siguientes números son **escenarios hipotéticos para planificar**, no cotizaciones de servicios ni pronósticos de rendimiento publicitario.

Costo mensual equivalente:

```text
infraestructura + dominio anual/12 + respaldos + correo
+ integraciones/IA + comisiones + horas de trabajo valoradas
```

Separar dos metas: cubrir desembolsos y cubrir también el trabajo editorial. Un sitio que paga hosting pero depende de muchas horas gratuitas no ha alcanzado sostenibilidad completa.

| Escenario hipotético | Gasto mensual de caja | Reserva operativa del 20% | Meta mensual |
| --- | ---: | ---: | ---: |
| Piloto austero | $30.000 CLP | $6.000 CLP | $36.000 CLP |
| Operación inicial | $60.000 CLP | $12.000 CLP | $72.000 CLP |
| Mayor uso de integraciones | $120.000 CLP | $24.000 CLP | $144.000 CLP |

Estos montos excluyen remuneraciones e impuestos específicos. Reemplazarlos por cotizaciones y costos propios antes de aprobar el presupuesto.

Para publicidad programática, usar **RPM de página** si se proyecta desde páginas vistas:

```text
ingreso estimado = páginas vistas / 1.000 × RPM de página
```

| Páginas vistas mensuales | RPM supuesto $500 CLP | RPM supuesto $1.500 CLP | RPM supuesto $3.000 CLP |
| --- | ---: | ---: | ---: |
| 10.000 | $5.000 | $15.000 | $30.000 |
| 30.000 | $15.000 | $45.000 | $90.000 |
| 100.000 | $50.000 | $150.000 | $300.000 |

Los RPM de la tabla son supuestos didácticos, no promedios del mercado chileno. El RPM real depende de demanda, formatos, cobertura, bloqueadores, tráfico y contenido. No mezclar RPM de anuncio con RPM de página.

Ejemplo de equilibrio: para cubrir una meta de $72.000 CLP con RPM hipotético de $1.500 CLP se necesitarían 48.000 páginas vistas mensuales. Alternativamente, dos patrocinios hipotéticos de $36.000 CLP podrían cubrir esa meta bruta; no se afirma que exista demanda a ese precio. Si hay comisiones o impuestos, calcular el ingreso neto antes de compararlo.

Antes de vender, validar interés comercial con una ficha que explique audiencia, formato, independencia y métricas. No inventar visitantes, alcance ni tasas de clics.

### 14.7 Plataformas para aportes: Ko-fi, Buy Me a Coffee, Patreon y otras

**Recomendación práctica:** habilitar una plataforma principal de aportes y, si hace falta, un medio local en CLP. Evitar ofrecer seis botones de pago desde el lanzamiento. Evaluar Ko-fi y Buy Me a Coffee mediante pruebas de cobro y retiro; comparar una alternativa local como Flow para reducir fricción del público chileno.

Las condiciones siguientes fueron consultadas el 6 de octubre de 2026. Son información general, no una aprobación de cuenta ni confirmación de una tarifa final para el proyecto.

| Opción | Modalidad | Disponibilidad y costos consultados | Uso recomendado |
| --- | --- | --- | --- |
| **Ko-fi** | Aportes únicos, mensuales y membresías | Ko-fi Free indica 0% de plataforma en aportes únicos; Standard aplica 5%. Mensuales y membresías tienen 5%. Se suman procesamiento y posible conversión/retiro de PayPal o Stripe | Buen candidato para comenzar con aportes únicos; probar cobro y retiro mediante una cuenta PayPal habilitada para Chile |
| **Buy Me a Coffee** | Aportes únicos y membresías | Publica 5% de plataforma, más procesamiento y cargos aplicables. Su lista de cobros de agosto de 2026 incluye Chile vía Stripe Express | Alternativa candidata para aportes simples con tarjetas; confirmar onboarding chileno, moneda y retiro en la cuenta concreta |
| **Patreon** | Membresías y apoyo recurrente | Nuevas páginas posteriores al 4 de agosto de 2025: plan estándar de 10%, más procesamiento, impuestos y otros cargos aplicables. Su guía incluye retiro de USD a CLP vía Payoneer | Considerar si existe comunidad recurrente y capacidad para gestionar membresías; no priorizar para solo cubrir hosting |
| **GitHub Sponsors** | Apoyo único o mensual a trabajo de código abierto | Incluye Chile; sin comisión de GitHub para aportes de cuentas personales y hasta 6% para organizaciones | Solo si se sostiene trabajo real de código abierto elegible; no usarlo como sustituto universal de donaciones al medio |
| **Flow** | Links de pago; suscripciones según producto y aprobación | Su página de links publica 2,89% + IVA para depósito al tercer día hábil o 3,19% + IVA al siguiente; sin costo fijo periódico | Candidato local para cobrar en CLP, confirmando que admite la actividad y el modelo de aporte |
| **Transferencia bancaria** | Aporte local directo | Condiciones dependen de la cuenta y del banco; conciliación manual | Alternativa complementaria para quien prefiera un medio local; no exponer datos personales innecesarios |

**Ko-fi:** comprobar la configuración de la cuenta, porque la documentación señala que los nuevos creadores comienzan con funciones Standard que aplican comisión también a aportes únicos. La modalidad de 0% debe seleccionarse cuando corresponda; no significa “sin comisiones” totales. Los pagos llegan a PayPal o Stripe. No asumir que el soporte de Chile por Stripe Express en otra plataforma permite abrir una cuenta Stripe independiente para Ko-fi.

**Buy Me a Coffee:** usar su listado específico de países para evaluar Chile, en lugar de inferir disponibilidad a partir de una cifra general de países del sitio. Realizar onboarding y una prueba antes de anunciarlo como medio operativo. Las condiciones de cuenta, retenciones y retiro siguen sujetas al proveedor.

**Patreon:** permite ingresos recurrentes, pero puede crear expectativas de beneficios y exigir más administración. Si se habilita, mantener públicas las fichas y la evidencia. Los beneficios opcionales podrían ser agradecimientos voluntarios, reuniones sobre metodología o un informe de gastos, sin acceso privilegiado a correcciones o decisiones editoriales.

**GitHub Sponsors:** “sponsor” también puede significar patrocinador directo del sitio. Son mecanismos distintos. GitHub Sponsors se reserva aquí para una eventual herramienta abierta, código, documentación o mantenimiento elegible, con finalidad y contabilidad diferenciadas. Publicar un repositorio por sí solo no garantiza aceptación.

**Flow y transferencias:** son medios de cobro, no una declaración de que el ingreso sea una donación exenta. Confirmar encaje contractual, tratamiento tributario y cómo documentar los pagos. Usar “aporte voluntario” en la interfaz mientras no se haya determinado la naturaleza jurídica correspondiente.

### 14.8 Página “Apoya el proyecto”

Contenido recomendado:

1. Para qué se usa el dinero: operación, investigación, mantenimiento y otros gastos identificados.
2. Meta mensual y avance sobre ingresos netos, indicando período y última actualización.
3. Aporte único como opción inicial y apoyo mensual claramente diferenciado cuando se habilite.
4. Montos sugeridos hipotéticos de $2.000, $5.000 y $10.000 CLP solo si el medio cobra en CLP; monto libre como alternativa.
5. Moneda efectiva, recurrencia y cargos conocidos antes de salir al checkout.
6. Aviso de que aportar no concede influencia sobre la selección, redacción o corrección de casos.
7. Explicación de cancelación y contacto para problemas de pagos.
8. Informe periódico de ingresos y gastos agregados.

Si la plataforma cobra en USD, mostrarlo claramente. No presentar una equivalencia aproximada en CLP como precio final; el tipo de cambio y cargos pueden variar.

**Diseño:** enlace “Apoya el proyecto” en navegación y una invitación breve al final de la ficha. Sin modal, cuenta regresiva, barra flotante ni interrupción. Abrir checkout alojado por el proveedor; la ficha continúa disponible. Las aportaciones no eliminan derechos de lectura para quienes no pagan.

No publicar nombres de aportantes por defecto. Solicitar autorización expresa para agradecimientos y permitir apoyo sin reconocimiento público. No tratar la lista de aportantes como una audiencia ideológica para anunciantes ni exportarla indiscriminadamente a sistemas comerciales.

### 14.9 Pasos para elegir y habilitar aportes

1. **Identidad y elegibilidad:** definir quién recibe los fondos y revisar país, actividad admitida y requisitos de verificación.
2. **Costo completo:** sumar plataforma, procesamiento, conversión, retiro, eventuales impuestos y devoluciones. Anotar mínimos y plazos.
3. **Prueba de escritorio:** comparar monto que paga la persona y neto esperado para un aporte pequeño y otro mediano.
4. **Prueba real controlada:** completar onboarding y probar checkout, confirmación y retiro mediante un procedimiento legítimo admitido por el proveedor; no simular identidades ni realizar transacciones incompatibles con sus condiciones.
5. **Recurrencia:** si se habilita, comprobar alta, aviso de periodicidad y cancelación, sin marcar suscripción por defecto.
6. **Conciliación:** verificar cómo se relaciona cada pago, comisión y depósito con los registros internos.
7. **Publicación:** activar una sola plataforma principal que haya pasado las verificaciones; añadir un medio local solo si resuelve una dificultad concreta.
8. **Revisión a 30 días:** evaluar neto, aportes completados, consultas y tiempo administrativo. No conservar una integración solo por reconocimiento de marca.

No es necesario integrar APIs de cobro en el MVP: un enlace al checkout puede bastar. Si posteriormente se usan webhooks, validar firmas, procesar eventos de forma idempotente y no confiar en una URL de retorno como prueba de pago. Nunca almacenar números de tarjeta.

### 14.10 Modelo combinado de ingresos

Separar aportes voluntarios, membresías, patrocinios con contraprestación publicitaria y anuncios programáticos en los registros. Aplicar las mismas reglas de independencia a grandes aportantes y patrocinadores.

Ejemplo puramente hipotético para una meta de caja de $72.000 CLP:

- 12 aportantes con **aporte neto** de $3.000 CLP: $36.000 CLP.
- Un patrocinio con **ingreso neto** de $36.000 CLP: $36.000 CLP.
- Total: $72.000 CLP; no cubre automáticamente remuneraciones.

El aporte bruto necesario será mayor si existen comisiones. No confundir 12 personas que aportaron una vez con 12 aportantes mensuales ni incluir promesas como ingresos recibidos. Usar reserva para que una baja temporal no obligue a aumentar publicidad.
