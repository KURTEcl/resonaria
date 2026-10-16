<?php

namespace App\Support;

use App\Models\EditorialCase;
use Illuminate\Support\Carbon;

class SiteDiscovery
{
    /**
     * @return list<array{loc: string, lastmod: ?string}>
     */
    public static function urls(): array
    {
        $urls = [];
        foreach (self::staticPages() as $path) {
            $urls[] = [
                'loc' => SiteUrl::of($path),
                'lastmod' => null,
            ];
        }

        $cases = EditorialCase::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get(['slug', 'published_at', 'reviewed_at', 'updated_at']);

        foreach ($cases as $case) {
            $stamp = $case->reviewed_at ?? $case->published_at ?? $case->updated_at;
            $urls[] = [
                'loc' => SiteUrl::of('/casos/'.$case->slug),
                'lastmod' => $stamp instanceof Carbon ? $stamp->toDateString() : null,
            ];
        }

        return $urls;
    }

    public static function sitemapXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach (self::urls() as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.self::xml($url['loc'])."</loc>\n";
            if ($url['lastmod']) {
                $xml .= '    <lastmod>'.self::xml($url['lastmod'])."</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return $xml;
    }

    public static function robotsTxt(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /api/',
            'Disallow: /preview/',
            'Disallow: /cuaderno',
            'Disallow: /mcp',
            '',
            'Sitemap: '.SiteUrl::of('/sitemap.xml'),
            '',
        ]);
    }

    public static function llmsTxt(): string
    {
        $origin = SiteUrl::origin();
        $lines = [
            '# Resonaria',
            '',
            '> Medio digital de actualidad, análisis y opinión política con cobertura nacional e internacional. Publica noticias con contexto y fuentes, junto con seguimiento de decisiones públicas.',
            '',
            '## Cómo citar',
            '',
            '- Usa la URL canónica de cada ficha: '.$origin.'/casos/{slug}',
            '- Indica la fecha de última revisión mostrada en la ficha.',
            '- Respeta el estado procedimental (propuesta, aprobada, en aplicación, etc.).',
            '',
            '## No hacer',
            '',
            '- No presentar una propuesta o anuncio como un retroceso ya consumado sin acto formal.',
            '- No omitir límites de evidencia indicados en la ficha.',
            '- No atribuir intenciones a autoridades sin respaldo documental.',
            '',
            '## Enlaces útiles',
            '',
            '- Inicio: '.$origin,
            '- Metodología: '.SiteUrl::of('/metodologia'),
            '- Fuentes: '.SiteUrl::of('/fuentes'),
            '- Correcciones: '.SiteUrl::of('/correcciones'),
            '- Mapa del sitio: '.SiteUrl::of('/sitemap.xml'),
            '- Índice ampliado: '.SiteUrl::of('/llms-full.txt'),
            '',
            '## Temas publicados',
            '',
        ];

        $cases = EditorialCase::query()
            ->published()
            ->orderBy('title')
            ->get(['slug', 'title']);

        if ($cases->isEmpty()) {
            $lines[] = 'Todavía no hay temas publicados.';
        } else {
            foreach ($cases as $case) {
                $lines[] = '- ['.$case->title.']('.SiteUrl::of('/casos/'.$case->slug).')';
            }
        }

        $lines[] = '';

        return implode("\n", $lines);
    }

    public static function llmsFullTxt(): string
    {
        $lines = [
            '# Resonaria — seguimientos publicados',
            '',
            'Cada entrada es un tema distinto. El slug de la URL es estable; el título visible puede actualizarse sin cambiar la dirección.',
            '',
        ];

        $cases = EditorialCase::query()
            ->published()
            ->with('category')
            ->orderBy('title')
            ->get();

        foreach ($cases as $case) {
            $reviewed = $case->reviewed_at ?? $case->published_at;
            $lines[] = '## '.$case->title;
            $lines[] = '';
            $lines[] = '- URL: '.SiteUrl::of('/casos/'.$case->slug);
            if ($case->category?->title) {
                $lines[] = '- Área: '.$case->category->title;
            }
            if ($case->procedural_status) {
                $lines[] = '- Etapa: '.$case->procedural_status;
            }
            if ($reviewed instanceof Carbon) {
                $lines[] = '- Revisado: '.$reviewed->toDateString();
            }
            $hook = PlainText::fromMarked($case->hook ?: $case->summary);
            if ($hook !== '') {
                $lines[] = '- Resumen: '.$hook;
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, string>
     */
    public static function files(): array
    {
        $files = [
            'sitemap.xml' => self::sitemapXml(),
            'robots.txt' => self::robotsTxt(),
            'llms.txt' => self::llmsTxt(),
            'llms-full.txt' => self::llmsFullTxt(),
        ];

        $token = trim((string) config('app.google_site_verification'));
        if ($token !== '') {
            $files['google'.$token.'.html'] = 'google-site-verification: google'.$token.".html\n";
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    public static function staticPages(): array
    {
        return [
            '/',
            '/metodologia',
            '/fuentes',
            '/correcciones',
            '/apoyar',
            '/ayuda',
        ];
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
