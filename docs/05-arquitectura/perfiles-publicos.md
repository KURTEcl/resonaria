# Perfiles públicos

Base de figuras públicas vinculadas al catálogo editorial (temas, fuentes, hitos y reacciones): autoridades del Estado (ejecutivo, Congreso, gobernadores, municipios, seremis, Poder Judicial y otros funcionarios), dirigentes y militantes de partidos, líderes sociales o gremiales, voces de prensa y análisis, y en general personas de interés público citadas en la cobertura.

## Modelo

- `public_profiles`: persona o figura con slug para `/perfiles/{slug}`.
- `profile_aliases`: nombres y cargos para detección en texto.
- `profile_roles`: cargos con vigencia (`started_on` / `ended_on`).
- `profile_social_accounts`: varias cuentas por persona (`personal`, `institucional`, `campana`) con tramos de vigencia; `use_for_avatar` prioriza la fuente de la foto.
- `profile_mentions`: enlace polimórfico a tema, fuente, hito, reacción o claim; `confidence` `confirmed` | `suggested`.

## Tipos (`kind`)

Definidos en `apps/api/config/profiles.php` (ver archivo para la lista vigente). Destacados: `diputado`, `senador` (y `legislador` genérico legacy), `gobernador`, `funcionario`, `dirigente`, `lider`, `interes_publico`. El MCP (`list_profiles`) expone `kind_options` y `creation_guide`; `add_profile_mention` crea el perfil activo en la primera vinculación cuando falta el slug.

## Operación

El bootstrap [`data/profiles/gobierno-ejecutivo.json`](../../data/profiles/gobierno-ejecutivo.json) refleja el gabinete del gobierno de José Antonio Kast (desde marzo de 2026; ajustes ministeriales según prensa). Los perfiles del gobierno anterior quedan con `status: archived` para no mezclarlos en el scan activo. Figuras fuera del JSON (alcaldes, voces de prensa, etc.) se crean al vincular la primera mención desde el agente editorial.

```bash
php artisan profiles:import /ruta/data/profiles/gobierno-ejecutivo.json
php artisan profiles:scan-published --dry-run
php artisan profiles:scan-published
php artisan profiles:refresh-avatars
```

Panel interno: `/cuaderno/perfiles` (misma puerta que la mesa de temas). API desk bajo `auth:sanctum` con ability `desk`.

## API pública

- `GET /api/v1/perfiles`
- `GET /api/v1/perfiles/{slug}`
- Los temas publicados incluyen `profiles[]` resumido cuando hay menciones confirmadas.

## Fotos

`profiles:refresh-avatars` obtiene la imagen de perfil de X (`pbs.twimg.com/profile_images`) y la copia a `storage/app/public/profiles/`. No se usan miniaturas de publicaciones.

## Termómetro (futuro)

Las métricas de campaña se derivarán de menciones confirmadas, fuentes de prensa y reacciones ligadas por cuenta; requiere metodología publicada antes de mostrar rankings públicos.
