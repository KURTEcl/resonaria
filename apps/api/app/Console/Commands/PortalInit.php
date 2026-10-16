<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PortalInit extends Command
{
    protected $signature = 'portal:init
                            {--name= : Portal display name}
                            {--tagline= : Short descriptor}
                            {--description= : Meta description}
                            {--locale=es : Content locale}
                            {--html-lang=es : HTML lang attribute}
                            {--tone=clear-formal : Editorial tone key}
                            {--content-types=tracking,news,analysis,opinion : Comma-separated types}
                            {--github= : GitHub repository URL}
                            {--support= : Support / Ko-fi URL}
                            {--site-url= : Public site URL (production)}
                            {--instagram= : Instagram profile URL}
                            {--x= : X profile URL}
                            {--non-interactive : Skip prompts; use flags or defaults}';

    protected $description = 'Generate portal bible and env snippets for a new installation';

    /** @var array<string, string> */
    private array $answers = [];

    public function handle(): int
    {
        $repoRoot = $this->repoRoot();
        $bibleDir = $repoRoot.'/portal/bible';
        $templates = $repoRoot.'/portal/bible-templates';

        if (! File::isDirectory($templates)) {
            $this->error('Missing portal/bible-templates in repository root.');

            return self::FAILURE;
        }

        $this->collectAnswers();

        File::ensureDirectoryExists($bibleDir);

        $this->writeBible($templates, $bibleDir);
        $this->writeEnvSnippets($repoRoot);

        $this->newLine();
        $this->info('Portal bible written to portal/bible/');
        $this->line('Next: merge portal/env-snippets/*.env into your .env files, then ./scripts/bootstrap-mysql.sh && ./dev-start.sh');
        $this->line('MCP token: cd apps/api && php artisan mcp:token');

        return self::SUCCESS;
    }

    private function repoRoot(): string
    {
        return dirname(base_path(), 2);
    }

    private function collectAnswers(): void
    {
        $interactive = ! $this->option('non-interactive') && $this->input->isInteractive();

        $defaults = [
            'name' => $this->option('name') ?: config('portal.name', 'My Portal'),
            'tagline' => $this->option('tagline') ?: 'What changes. Who is affected.',
            'description' => $this->option('description') ?: 'A traceable, source-driven publication.',
            'locale' => $this->option('locale') ?: 'es',
            'html_lang' => $this->option('html-lang') ?: 'es',
            'tone' => $this->option('tone') ?: 'clear-formal',
            'content_types' => $this->option('content-types') ?: 'tracking,news,analysis,opinion',
            'github' => $this->option('github') ?: 'https://github.com/kurtecl/resonaria',
            'support' => $this->option('support') ?: 'https://ko-fi.com/kurtecl',
            'site_url' => $this->option('site-url') ?: (string) config('app.url'),
            'instagram' => $this->option('instagram') ?: '',
            'x' => $this->option('x') ?: '',
        ];

        if ($interactive) {
            $this->components->info('Portal install wizard — define your instance (not the upstream Resonaria demo).');
            $defaults['name'] = $this->ask('Portal name', $defaults['name']);
            $defaults['tagline'] = $this->ask('Site tagline (short)', $defaults['tagline']);
            $defaults['description'] = $this->ask('Meta description', $defaults['description']);
            $defaults['locale'] = $this->ask('Content locale (e.g. es, en)', $defaults['locale']);
            $defaults['html_lang'] = $this->ask('HTML lang (e.g. es-CL, en-US)', $defaults['html_lang'] ?: $defaults['locale']);
            $defaults['tone'] = $this->anticipate(
                'Editorial tone',
                ['clear-formal', 'conversational', 'technical'],
                $defaults['tone'],
            );
            $defaults['content_types'] = $this->ask(
                'Content types (comma-separated: tracking, news, analysis, opinion)',
                $defaults['content_types'],
            );
            $defaults['github'] = $this->ask('GitHub URL for this project', $defaults['github']);
            $defaults['support'] = $this->ask('Support URL (Ko-fi, Patreon, etc.)', $defaults['support']);
            $defaults['site_url'] = $this->ask('Public site URL (production)', $defaults['site_url']);
            $defaults['instagram'] = $this->ask('Instagram URL (optional, empty to skip)', $defaults['instagram']);
            $defaults['x'] = $this->ask('X URL (optional, empty to skip)', $defaults['x']);
        }

        $slug = Str::slug($defaults['name']) ?: 'portal';
        $defaults['agent_email'] = "agent@{$slug}.local";

        $this->answers = $defaults;
    }

    private function writeBible(string $templates, string $bibleDir): void
    {
        $replacements = [
            '{{PORTAL_NAME}}' => $this->answers['name'],
            '{{PORTAL_TAGLINE}}' => $this->answers['tagline'],
            '{{PORTAL_DESCRIPTION}}' => $this->answers['description'],
            '{{PORTAL_LOCALE}}' => $this->answers['locale'],
            '{{PORTAL_TONE}}' => $this->answers['tone'],
            '{{PORTAL_CONTENT_TYPES}}' => $this->answers['content_types'],
            '{{PORTAL_GITHUB_URL}}' => $this->answers['github'],
            '{{PORTAL_SUPPORT_URL}}' => $this->answers['support'],
            '{{PORTAL_SITE_URL}}' => $this->answers['site_url'],
            '{{PORTAL_MCP_URL}}' => rtrim($this->answers['site_url'], '/').'/mcp',
        ];

        foreach (File::allFiles($templates) as $file) {
            if ($file->getExtension() !== 'md' && $file->getExtension() !== 'json') {
                continue;
            }
            $relative = Str::after($file->getPathname(), $templates.DIRECTORY_SEPARATOR);
            $contents = File::get($file->getPathname());
            foreach ($replacements as $search => $replace) {
                $contents = str_replace($search, $replace, $contents);
            }
            File::put($bibleDir.'/'.$relative, $contents);
        }
    }

    private function writeEnvSnippets(string $repoRoot): void
    {
        $snippetDir = $repoRoot.'/portal/env-snippets';
        File::ensureDirectoryExists($snippetDir);

        $a = $this->answers;
        $apiPort = '8017';
        $webPort = '3017';

        $rootEnv = <<<ENV
# Generated by portal:init — merge into repository root .env
APP_NAME={$a['name']}
API_PORT={$apiPort}
WEB_PORT={$webPort}
NUXT_PUBLIC_API_BASE=http://127.0.0.1:{$apiPort}/api
NUXT_PUBLIC_SITE_URL={$a['site_url']}
NUXT_PUBLIC_PORTAL_NAME={$a['name']}
NUXT_PUBLIC_PORTAL_TAGLINE={$a['tagline']}
NUXT_PUBLIC_PORTAL_DESCRIPTION={$a['description']}
NUXT_PUBLIC_PORTAL_GITHUB_URL={$a['github']}
NUXT_PUBLIC_PORTAL_SUPPORT_URL={$a['support']}
NUXT_PUBLIC_SITE_LANG={$a['html_lang']}
ENV;

        $apiEnv = <<<ENV
# Generated by portal:init — merge into apps/api/.env
APP_NAME={$a['name']}
APP_URL=http://127.0.0.1:{$apiPort}
FRONTEND_URL=http://127.0.0.1:{$webPort}
PORTAL_NAME={$a['name']}
PORTAL_TAGLINE={$a['tagline']}
PORTAL_DESCRIPTION={$a['description']}
PORTAL_LOCALE={$a['locale']}
PORTAL_HTML_LANG={$a['html_lang']}
PORTAL_EDITORIAL_TONE={$a['tone']}
PORTAL_CONTENT_TYPES={$a['content_types']}
PORTAL_GITHUB_URL={$a['github']}
PORTAL_SUPPORT_URL={$a['support']}
PORTAL_SOCIAL_INSTAGRAM={$a['instagram']}
PORTAL_SOCIAL_X={$a['x']}
PORTAL_AGENT_EMAIL={$a['agent_email']}
ENV;

        File::put($snippetDir.'/root.env', $rootEnv);
        File::put($snippetDir.'/api.env', $apiEnv);

        $mcp = [
            'mcpServers' => [
                'resonaria' => [
                    'url' => rtrim($a['site_url'], '/').'/mcp',
                    'headers' => [
                        'Authorization' => 'Bearer PASTE_TOKEN_FROM_php_artisan_mcp:token',
                    ],
                ],
            ],
        ];
        File::put($snippetDir.'/mcp.json', json_encode($mcp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }
}
