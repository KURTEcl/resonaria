# Comparador

Especificación funcional: [diseno-claro-v3.md](diseno-claro-v3.md) §16. API: `GET /api/v1/comparisons`.

## Flujo

1. Programa o grupo comparable
2. Período y base
3. Métrica: Presupuesto o Cobertura
4. Gráfico + tabla + fuentes
5. Compartir URL / descargar CSV

## Reglas de datos

- Backend calcula series, unidades, advertencias y huecos.
- Frontend dibuja; no fórmulas distintas.
- Dato ausente ≠ cero.
- CSV con referencias; escapar celdas que parezcan fórmulas.

## Visual

- Verde = Antes; Violeta = Propuesta/Después.
- Barras con base cero; no mezclar dinero y personas en un eje.

## MVP

- Activar solo con conjuntos comparables suficientes.
- Etiqueta “datos de prueba” en entornos demo.
