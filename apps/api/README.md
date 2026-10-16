<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## MCP editorial

Un agente (Claude, ChatGPT o Codex) carga temas y reacciones en `POST {APP_URL}/mcp`.

Genera un token con `php artisan mcp:token` y envíalo en el header `Authorization: Bearer …`. El token publica lo que el agente cargue. Las tools son `list_categories`, `list_topics`, `get_topic`, `create_topic`, `update_topic`, `set_topic_cover`, `add_source`, `add_reaction`, `add_update`, `add_claim_check` y `add_link`.

### Portadas de caso (WebP)

Las ilustraciones viven en `apps/web/public/casos/{slug}/cover.webp`. Solo se conserva la versión optimizada (WebP, ancho máx. 1920 px, calidad ~82).

- **Agentes (MCP):** tool `set_topic_cover` con `slug` + `image_base64` o `image_url` (mismo token que `create_topic`, p. ej. `QUEPERDIMOS_MCP_TOKEN`). Ver [`docs/07-ia/configurar-chatgpt-mcp.md`](../../docs/07-ia/configurar-chatgpt-mcp.md).
- **Cuaderno / humano:** `POST /api/v1/desk/cases/{slug}/cover` con `multipart` campo `cover` (JPEG/PNG/WebP).
- **CLI:** `php artisan topics:optimize-cover {slug}` convierte `cover.jpg` (u otra ruta en `og_image_url`) y actualiza la base de datos. `topics:optimize-cover --all` recorre el catálogo.

Opcional: `WEB_PUBLIC_PATH` en `.env` si el API no está en `apps/api` junto al front.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
