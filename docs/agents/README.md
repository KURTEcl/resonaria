# Agentes de IA (portables)

Definiciones para usar en **Cursor, Claude, ChatGPT** u otro LLM. No dependen de un producto concreto.

## Roles

| Agente | Archivo | Función |
| --- | --- | --- |
| Desarrollo | [dev-portal.md](dev-portal.md) | Código, infra, self-host |
| Periodista | [periodista-investigador.md](periodista-investigador.md) | Fuentes, expedientes, borradores |
| Redes | [redes-contenido.md](redes-contenido.md) | Bajadas, hashtags, previews |

## Cadena editorial

1. **Periodista** → expediente estructurado + `in_review`
2. **Humano** → publica
3. **Redes** → variantes sobre versión publicada

Ningún agente **publica hechos nuevos** sin revisión humana ([../07-ia/politica-de-uso.md](../07-ia/politica-de-uso.md)).

Errores que se devuelven a revisión (detalle en el periodista): ficha en prosa plana, sin negritas ni marcas de color; titulares que repiten «Presupuesto 2027» u otra fórmula compartida en vez de nombrar el programa afectado; vocabulario de doble sentido (p. ej. «pico») o lenguaje de trámite legislativo en noticias en desarrollo ([estados-y-lenguaje.md](../01-producto/estados-y-lenguaje.md)).

Tras publicar, el periodista revisa cada tema: busca noticias y redes que aún no estén en la ficha y las carga con `add_source` / `add_reaction`. Si circula una idea como si fuera un hecho, la contrasta con `add_claim_check`. Si otra nota del portal da contexto o es consecuencia, las asocia con `add_link` y `[[slug|texto]]` ([enlaces-internos.md](../03-metodologia/enlaces-internos.md)).

## Conexión al sistema

### REST (cualquier cliente)

- OpenAPI: [../05-arquitectura/contratos-api.md](../05-arquitectura/contratos-api.md)
- Token con scopes mínimos

### MCP (opcional)

- Tools: [../05-arquitectura/integracion-mcp.md](../05-arquitectura/integracion-mcp.md)
- `Mcp::local` en dev, `Mcp::web` en staging/prod

### Sin integración

- Copiar/pegar JSON de expediente según esquemas en cada agente; humano carga en admin.

## Plantilla común

Cada agente incluye: misión, límites, entradas/salidas, checklist, flujo, prompt sistema, handoff, métricas de calidad.
