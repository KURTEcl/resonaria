# Resonaria: design system (open source)

UI kit for the Resonaria publication portal. Each instance sets editorial positioning in `portal/bible/`; this file describes **layout and interaction** either way.

**Tokens:** [design/tokens.json](design/tokens.json) · [design/tokens.css](design/tokens.css) · [docs/02-diseno-ux/tokens-y-tipografia.md](docs/02-diseno-ux/tokens-y-tipografia.md)  
**Code:** `apps/web/app/assets/css/main.css`  
**Upstream:** [github.com/kurtecl/resonaria](https://github.com/kurtecl/resonaria)

## Estructura

- **Rail lateral**: Inicio · categorías (filtran la portada, multiselección) · Fuentes, Metodología, Ayuda · redes · Apoyar. Separadores de 1px agrupan.
- **Header**: logo, buscador, menús Estado y Tipo (solo en portada), Apoyar.
- **Portada** (`/`): presentación, temas agrupados por categoría (título y `lead` a la izquierda, entradas a la derecha), guía de estados, cierre de apoyo. `/casos` redirige a `/` conservando filtros.
- **Ficha** (`/casos/:slug`): ruta, estado, título, gancho, bloque de registro (etapa, evidencia, efecto observado, tipo, revisión, próxima revisión), Qué cambia / A quién afecta, Antes y con el cambio, puntos (Cómo va, detalle, fuentes), versión y enlaces a metodología y correcciones. Mitad derecha: En redes, fija al hacer scroll; en móvil, pestaña. Cómo va resume si la propuesta negativa avanza, puede revertirse o aún no se ve el rumbo.
- **Páginas internas**: plantilla `.doc`: encabezado con lede, secciones con título a la izquierda y cuerpo a la derecha (definiciones, pasos numerados, tablas, listas de enlaces).

## Reglas

1. Un tema siempre muestra estado, etapa y fecha de revisión antes que cualquier otra cosa.
2. El estado lleva texto e ícono; nunca solo color.
3. Hecho, testimonio y opinión se separan visualmente: las redes van aparte y declaran que no prueban cifras.
4. Separar con filetes (1px `--border`, 2px `--ink` para inicio de sección). Sin tarjetas repetidas, sin sombras salvo menús desplegables.
5. Sin kickers ni rótulos en mayúsculas sobre los títulos, sin glifos unicode como íconos (flechas, estrellas), sin bordes laterales de color, sin degradados, sin cifras héroe.
6. Texto público en español de Chile, condicional cuando la medida no está consumada. Nada de copy de desarrollo en la interfaz.
7. Violeta solo para Apoyar. Ámbar solo para “En discusión”.
8. Foco visible en verde petróleo; objetivos táctiles de al menos 40px; sin desborde horizontal a 390px.
