<?php

return [
    /** Las menciones del escaneo (profiles:scan-published) se guardan confirmadas. */
    'scan_auto_confirm' => env('PROFILES_SCAN_AUTO_CONFIRM', true),

    'kinds' => [
        'ejecutivo' => 'Ejecutivo nacional (gabinete y subsecretarías)',
        'diputado' => 'Diputado o diputada',
        'senador' => 'Senador o senadora',
        'legislador' => 'Legislador (genérico; preferir diputado o senador)',
        'gobernador' => 'Gobernador regional',
        'alcalde' => 'Alcalde o alcaldesa',
        'concejal' => 'Concejal',
        'seremi' => 'Seremi o autoridad regional de línea',
        'funcionario' => 'Funcionario o autoridad del Estado (Poder Judicial, fiscalía, contraloría, FF.AA., etc.)',
        'dirigente' => 'Dirigente o militante de partido político',
        'lider' => 'Líder social, gremial, sindical o comunitario',
        'periodista' => 'Periodista',
        'economista' => 'Economista o académico de economía',
        'comentarista' => 'Comentarista o cuentista político',
        'profesional' => 'Profesional o experto (otra disciplina)',
        'organizacion' => 'Organización, gremio, colegio profesional o asociación',
        'interes_publico' => 'Persona de interés público (relevante en la nota sin otro kind claro)',
        'otro' => 'Otra figura (último recurso)',
    ],

    /**
     * Guía breve para agentes (MCP) al crear perfiles con add_profile_mention.
     */
    'agent_creation_guide' => <<<'TXT'
Crea perfil y vincula a la nota cuando la persona importa en el relato: protagonista, responsable de cartera, autoridad que decide, legislador que impulsa o frena, vocero, fuente citada con nombre o figura que el lector buscará.
Ámbito Estado: ejecutivo, diputado, senador, gobernador, alcalde, concejal, seremi, funcionario (magistrados, fiscales, uniformados con rol público, etc.). Política: dirigente (partidos, coaliciones). Sociedad: lider (movimientos, gremios, comunidades). Instituciones: organizacion (colegios profesionales, asociaciones, gremios como actor). Medios y análisis: periodista, economista, comentarista, profesional. interes_publico si es relevante pero no encaja; otro solo si ningún kind aplica.
add_profile_mention crea el perfil activo y la mención confirmada si el slug no existe (display_name recomendado; si falta, se infiere del slug). list_profiles antes de crear evita duplicados. gobierno-ejecutivo.json cubre el gabinete nacional.
Aliases: nombre completo y variantes; cargos («senador por…», «presidente del PS») si ayudan al scan. Cuentas X/Instagram en cuaderno cuando existan.
TXT,
    'role_levels' => [
        'presidente' => 'Presidente',
        'ministro' => 'Ministro o ministra',
        'subsecretario' => 'Subsecretario o subsecretaria',
        'otro' => 'Otro cargo',
    ],
    'account_kinds' => [
        'personal' => 'Personal',
        'institucional' => 'Institucional',
        'campana' => 'Campaña',
    ],
    'mention_roles' => [
        'protagonista' => 'Protagonista',
        'mencionado' => 'Mencionado',
        'autor_declaracion' => 'Autor de declaración',
        'responsable_cartera' => 'Responsable de cartera',
    ],
    'alias_types' => [
        'nombre_completo' => 'Nombre completo',
        'apellido' => 'Apellido',
        'cargo_generico' => 'Cargo genérico',
        'cuenta' => 'Cuenta o handle',
    ],
];
