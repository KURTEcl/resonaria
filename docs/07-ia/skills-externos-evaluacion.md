# Evaluación de skills externos (redes + motion)

Revisión de tres colecciones frente al flujo de Qué Perdimos: ficha publicada → video HyperFrames 9:16 + carrusel 4:5 + bajadas (`docs/07-ia/base-publicaciones-redes.md`, `docs/agents/redes-contenido.md`).

## Resumen

| Repositorio | Adoptar | Cómo |
| --- | --- | --- |
| [heygen-com/hyperframes-community-skills](https://github.com/heygen-com/hyperframes-community-skills) | **Sí, parcial** | Instalar 1–2 skills concretos; no el paquete entero |
| [amigoscode/infographics-skill](https://github.com/amigoscode/infographics-skill) | **Sí, adaptado** | Fork o `config.json` propio; solo cifras de la ficha |
| [charlie947/social-media-skills](https://github.com/charlie947/social-media-skills) | **Sí, muy parcial** | 2–4 skills; el resto es LinkedIn/personal brand |

**Pipeline recomendado:** boceto de imagen para aprobar identidad y composición → piezas con datos en HyperFrames; EffectCraft como candidato a animar tratamiento manual tras un piloto. FilmCraft queda para montaje con metraje.

## Revisión de `awesome-claude-video-skills`

La colección enlazada sirve como catálogo e inspiración, no como ranking visual: su comparativa pública prueba 16 skills en videos de 15 segundos, y verifica reproducción, tamaño/duración, legibilidad y movimiento; no califica objetivamente el atractivo. Ese formato de prueba tampoco es equivalente a nuestras piezas editoriales de al menos un minuto.

| Recurso | Encaje en QP | Decisión |
| --- | --- | --- |
| [iart-ai/data-animation-skills](https://github.com/iart-ai/data-animation-skills) | Gráficas animadas, contadores y plantillas de infografía con números fijados desde datos; contempla CSV y exportación en lote | **Candidato opcional a piloto.** El flujo actual parte de ficha JSON y HyperFrames ya anima barras/contadores; probar si aporta un tratamiento útil sin perder control de escala, estado y notas editoriales. |
| [motion-graphics de la colección](https://github.com/zhuyansen/awesome-claude-video-skills) | La propia evaluación la describe como orientada a talking head y overlays | **No es la opción para estas piezas sin metraje.** |
| Remotion Skills | Autoría con React y render | **No sumar por ahora.** Duplica el stack HTML/HyperFrames sin ventaja demostrada en este caso. |

Skills locales de HyperFrames, `motion-design` e `imagegen` ya cubren dirección de arte, ritmo, animación y exploración de imágenes maestras. Para la secuencia imagen-primero, generar referencias/texturas como arte de base y componer títulos, cifras y gráficas en capas vectoriales editables; no confiar números finales a un generador de imágenes.

---

## 1. HyperFrames Community Skills

Instalación por skill:

```bash
npx skills add heygen-com/hyperframes-community-skills --list
npx skills add heygen-com/hyperframes-community-skills --skill <nombre> -g -y
```

### Recomendados

| Skill | Utilidad para QP | Notas |
| --- | --- | --- |
| **p5-paint-animation** | **Alta** | Complementa el doodle (`yo-elijo-mi-pc-doodle.html`): frases que se “escriben”, repintado de fotos, clips cortos estilo pintura. 100 % código, sin modelo de imagen. Requiere Node 22+, `ffmpeg`, `sh scripts/setup.sh` una vez. |
| **vox-explainer** | **Media (proceso)** | Buen pipeline (brief → diseño estático → continuidad → seam gate). El look Vox/collage **no** encaja con la marca; usar **modo source** con la ficha y override visual en el brief. |

### Descartar o muy baja prioridad

| Skill | Motivo |
| --- | --- |
| camera-3d-captions | Pensado para talking head + captions 3D |
| duo | Meme / dos pantallas en “foldable” |
| day-in-my-life, session-story | Narrativa agente–usuario, no periodismo de datos |
| prod-by-claude | Strudel / música live-coding |
| x-posting-license | Tarjeta animada de perfil X |

---

## 2. Infographics Skill (Amigoscode)

**Qué hace:** prompt → imagen “hand-drawn” con **Gemini** → plantilla HTML con logo/footer → screenshot Playwright → PNG + `caption.txt`.

**Encaje:** carruseles o láminas **estáticas** 4:5 cuando conviene un diagrama tipo pizarra (cómo fluye el trámite, barras pareadas dibujadas). **No** sustituye el video HyperFrames; puede alimentar **una o dos slides** del carrusel.

**Adaptación obligatoria**

1. `config.json`: footer `queperdimos.cl`, logo desde `docs/02-diseno-ux/assets/brand/`, `outputDir` bajo `videos/redes-base/salidas/`.
2. Reescribir el prompt template: temas de **política pública / presupuesto**, no Java/backend; prohibir inventar cifras (solo placeholders que el agente rellena desde la ficha).
3. Revisión humana: Gemini puede “dibujar” números incorrectos → contrastar con `content_version`.

**Requisitos:** `GEMINI_API_KEY`, `npm install` en el repo del skill, Playwright.

```bash
git clone https://github.com/amigoscode/infographics-skill.git
# Primera ejecución: onboarding → config Qué Perdimos
```

---

## 3. Social Media Skills (Charlie Hills)

17 skills orientados a **LinkedIn + voz personal** (`about-me.md`, `voice.md`). Instalar solo piezas sueltas:

```bash
npx skills add charlie947/social-media-skills --skill hook-generator -g -y
```

### Recomendados (con adaptación)

| Skill | Uso en QP |
| --- | --- |
| **hook-generator** | Primeras líneas de caption / slide 1; anclar en estado + dato de la ficha |
| **graphic-designer** | HTML/CSS → PNG cuando no baste `carousel/*.html` |
| **gemini-carousel** | Brief slide a slide + prompts Gemini; **sustituir** `brand-kit.md` por identidad QP y formato **1080×1350** IG (el skill dice LinkedIn pero el formato coincide) |
| **gemini-infographic** | Una lámina explicativa raster; misma regla de no inventar datos |

### Útiles solo en casos concretos

| Skill | Cuándo |
| --- | --- |
| **post-formatter** | Estructuras PAS/STAR para captions largas (Instagram) |
| **pinned-comment** | Comentario fijado + imagen (menos prioritario que la ficha) |
| **niche-research** | Ideas de ángulo; **no** reemplaza al periodista ni fuentes primarias |

### No adoptar tal cual

| Skill | Motivo |
| --- | --- |
| voice-builder, post-writer, newsletter-voice, profile-optimizer | Voz personal LinkedIn; ya tenemos `redes-contenido.md` + lenguaje condicional |
| **reels-scripting** | Guión para Reels “hablados” vía Apify + análisis de video; nuestro Reel es **motion graphic sin locución** |
| analytics-dashboard, post-scorer | Métricas LinkedIn del autor, no del producto QP |
| youtube-thumbnail | YouTube horizontal |
| content-matrix | Pilares de creador individual |

---

## Stack sugerido (sin duplicar lo que ya tenemos)

Ya instalados o en uso en el repo:

- HyperFrames + `motion-graphics` / `hyperframes-motion-director`
- LottieFiles `motion-design`
- MCP `user-queperdimos` (ficha)
- Carruseles HTML en `videos/redes-base/carousel/`

**Skills ya disponibles en el entorno local:**

- `hyperframes` + `general-video`: composiciones de 60–90 s, escenas, audio y render.
- `hyperframes-creative` + `hyperframes-animation` + `motion-design`: dirección de arte, ritmo y movimiento legible.
- `p5-paint-animation`: trazos dibujados y escritura a mano determinista como recurso gráfico.
- `faceless-explainer`: estructura narrativa para explicar notas y temas sin material de sitio; usar como apoyo de guion, respetando el límite de evidencia de la ficha.
- `impeccable`: revisión y consistencia de interfaces; no genera por sí sola assets editoriales.

Para este proyecto la combinación más útil es `hyperframes` + `hyperframes-creative` + `hyperframes-animation`; sumar `p5-paint-animation` solo cuando una ilustración a mano ayude a explicar el cambio. No instalar `hook-generator` ni generadores de imágenes hasta tener una prueba concreta: las plantillas y los datos existentes resuelven primero la necesidad.

## FilmCraft y EffectCraft

Instaladas en `/Applications/FilmCraft.app` (0.6.0) y `/Applications/EffectCraft.app` (0.8.0).

| Herramienta | Aporta | Recomendación para Que Perdimos |
| --- | --- | --- |
| HyperFrames (actual) | Datos y textos versionables, layouts 9:16/4:5, animación reproducible y render desde HTML | Mantener como herramienta principal para videos con cifras, estados, hitos y URL. Facilita revisar y repetir la pieza desde una ficha congelada. |
| FilmCraft | Edición no lineal, montaje de metraje, ritmo y audio | Usar cuando haya entrevistas, terreno, archivos o clips que ensamblar. No sería la base para generar carruseles o animaciones de cifras. |
| EffectCraft | Capas, texto animado, keyframes, máscaras, efectos, composición y exportación | **La candidata más afín para animar la identidad A+B**, intros/outros y recursos gráficos compuestos. Probar una entrada tipográfica, una transformación de cifra y una transición; verificar exportación, tipografías y estabilidad de la versión instalada. |

El catálogo de ArtCraft mantiene ambas apps en desarrollo. El repositorio de EffectCraft describe composición por capas, texto, keyframes, expresiones, 3D, render queue y proyectos JSON versionables; encaja directamente con la animación manual de piezas editoriales ([catálogo oficial](https://getartcraft.com/apps), [EffectCraft](https://github.com/storytold/effectcraft)). Por su estado joven, probar con un concepto sencillo antes de sumarlo al flujo recurrente. FilmCraft es editor de video y se reserva para montaje cuando haya metraje ([FilmCraft](https://github.com/storytold/filmcraft)). Mantener HyperFrames como ruta repetible desde datos/ficha y usar EffectCraft para la variante manual si el piloto valida exportación y control de calidad.

**No instalar por defecto:** reels-scripting (coste Apify + enfoque distinto), vox-explainer look completo, el paquete entero de social-media-skills.

---

## Seguridad

Los tres repos son skills de agente: pueden pedir red, APIs de pago y escritura en disco. Revisar cada `SKILL.md` antes de `-g`. Community skills de HeyGen llevan aviso explícito en su README.

---

## Próximo paso sugerido

1. Usar `videos/redes-base/brand-kit.md` y la propuesta `videos/redes-base/propuestas/direccion-visual-a-b.svg` como punto de partida visual.
2. Probar `p5-paint-animation` en un dibujo del próximo caso; ya está disponible localmente y no requiere generador de imagen.
3. Mantener `hook-generator`, Gemini y Apify fuera del flujo hasta contar con un caso de uso y validar su costo y riesgo de cifras mal dibujadas.
