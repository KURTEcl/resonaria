<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueMcpToken extends Command
{
    protected $signature = 'mcp:token';

    protected $description = 'Crea un token Sanctum para conectar un agente al MCP editorial';

    public function handle(): int
    {
        $user = User::query()->firstOrCreate(
            ['email' => config('portal.agent_email')],
            ['name' => 'Editorial agent', 'password' => Str::password()],
        );

        $token = $user->createToken('mcp')->plainTextToken;
        $url = rtrim((string) config('app.url'), '/').'/mcp';

        $this->line($token);
        $this->newLine();
        $this->info('POST '.$url);
        $this->info('Header: Authorization: Bearer <token>');

        return self::SUCCESS;
    }
}
