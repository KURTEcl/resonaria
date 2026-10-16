<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateDeskUser extends Command
{
    protected $signature = 'desk:user {email} {--name=Mesa} {--password=}';

    protected $description = 'Crea o actualiza la persona que entra a la mesa de contenido';

    public function handle(): int
    {
        $password = (string) ($this->option('password') ?: Str::password(20));

        User::query()->updateOrCreate(
            ['email' => $this->argument('email')],
            ['name' => $this->option('name'), 'password' => $password],
        );

        $this->info('Listo. Entra en /cuaderno?c= con el valor de DESK_GATE.');
        $this->line('Email: '.$this->argument('email'));
        $this->line('Clave: '.$password);

        return self::SUCCESS;
    }
}
