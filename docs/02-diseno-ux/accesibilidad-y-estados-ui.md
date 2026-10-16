# Accesibilidad y estados de interfaz

Checklist: [diseno-claro-v3.md](diseno-claro-v3.md) §12, §18 y §21.

## Accesibilidad

- Iconos con nombre accesible; no depender solo del tooltip en táctil.
- Contraste verificado en texto y estados de foco.
- Navegación por teclado en rail, filtros y pestañas.
- Gráfico con tabla equivalente.
- Lectura a **360px** sin scroll horizontal global.
- `lang="es-CL"`.

## Estados obligatorios

| Situación | UI |
| --- | --- |
| Carga | Skeleton sin datos ficticios |
| Sin resultados | Explicar filtros + limpiar |
| Error red | Reintento |
| Datos insuficientes | Qué falta; no dibujar ceros |
| Revisión vencida | “Actualización pendiente” |
| Aportes no configurados | Ocultar checkout falso |

## Publicidad

Máximo un espacio por página; sin popup ni video automático (plan v1.0 §14).
