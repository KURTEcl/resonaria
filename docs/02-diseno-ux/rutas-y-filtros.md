# Rutas y filtros

Ver especificación completa en [diseno-claro-v3.md](diseno-claro-v3.md) §13–14. API: [../05-arquitectura/busqueda-y-filtros.md](../05-arquitectura/busqueda-y-filtros.md).

## Rutas principales

| Elemento | Ruta |
| --- | --- |
| Portada | `/` |
| Catálogo | `/casos` |
| Ficha | `/casos/{slug}` |
| Fuentes ancla | `/casos/{slug}#fuentes` |
| Comparador | `/comparador` |
| Correcciones | `/correcciones` |

## Filtros en URL

```text
/casos?area=educacion&estado=en-discusion&tipo=presupuesto&q=becas
```

- OR dentro de `area`, `estado`, `tipo`; AND entre grupos.
- `Limpiar filtros` resetea todos + `q`.
- Portada: filtros solo afectan “Temas en seguimiento” (texto explicativo visible).

## Móvil

Panel temporal con **Aplicar** / **Limpiar**; cancelar conserva selección previa.

## Persistencia al navegar

Volver desde ficha al catálogo conserva query.
