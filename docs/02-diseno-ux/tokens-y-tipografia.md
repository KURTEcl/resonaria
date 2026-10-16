# Tokens y tipografía

Fuente de verdad: `apps/web/app/assets/css/main.css` y [DESIGN.md](../../DESIGN.md). Reemplaza los tokens de [diseno-claro-v3.md](diseno-claro-v3.md) §3–4.

## CSS variables

```css
:root {
  --canvas: #f4f6f8;      /* fondo de página */
  --surface: #ffffff;     /* rail, campos, comparación */
  --text: #1c2430;        /* cuerpo */
  --ink: #12302d;         /* títulos y datos */
  --muted: #566070;       /* texto secundario */
  --faint: #7a8494;       /* fechas, metadatos */
  --border: #e0e5eb;      /* filetes finos */
  --rule: #d3dae2;        /* separador de grupo */
  --primary: #0f766e;     /* enlaces, foco, estado activo */
  --primary-hover: #0b5f59;
  --primary-soft: #e3f2f0;
  --secondary: #6a4cc0;   /* solo Apoyar y marca */
  --rail-width: 64px;
  --radius: 12px;
  --radius-sm: 8px;
  --measure: 68ch;
}
```

Estados públicos, siempre con texto e ícono propio (no solo color):

| Estado | Texto | Fondo | Ícono |
| --- | --- | --- | --- |
| En discusión | `#7a5208` | `#fbf0d9` | círculo punteado |
| Aprobado | `#0b5f59` | `#e3f2f0` | visto |
| En aplicación | `#1f4f8f` | `#e4ecf8` | círculo lleno |
| Revertido | `#4a5363` | `#eceff2` | flecha de retorno |

## Tipografía

- Familia única: **Schibsted Grotesk** (variable 400–700, Google Fonts), fallback `system-ui`. Grotesca de prensa: legible en párrafo y firme en titulares. Inter queda descartada.
- Cuerpo 16px / 1.6; lectura larga 17px / 1.7; medida máxima 68ch.
- Titulares 650, interletraje negativo (−0.03em a −0.04em). Rótulos de datos 12.5–13px, 600.
- Fechas y cifras con `font-variant-numeric: tabular-nums`.

## Acentos

- Verde petróleo: enlaces, foco, filtros activos, “Con el cambio”.
- Violeta: solo Apoyar y la marca.
- Ámbar: solo estado “En discusión”.

## Marca

La identidad aprobada usa la Q/lupa con segmento violeta separado. Ver [identidad-visual.md](identidad-visual.md) para archivos, descriptor y reglas.
