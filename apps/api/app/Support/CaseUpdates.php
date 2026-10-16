<?php

namespace App\Support;

class CaseUpdates
{
    /**
     * @return list<string>
     */
    public static function directionKeys(): array
    {
        return array_keys(self::directions());
    }

    /**
     * @return array<string, string>
     */
    public static function directions(): array
    {
        return [
            'riesgo' => 'La medida avanza',
            'reversion' => 'Puede revertirse',
            'incierto' => 'Aún no se ve el rumbo',
        ];
    }

    /**
     * @return list<string>
     */
    public static function kindKeys(): array
    {
        return array_keys(self::kinds());
    }

    /**
     * @return array<string, string>
     */
    public static function kinds(): array
    {
        return [
            'tramite' => 'Trámite',
            'votacion' => 'Votación',
            'anuncio' => 'Anuncio',
            'redes' => 'Conversación pública',
            'fuente' => 'Fuente',
            'otro' => 'Otro',
        ];
    }

    /**
     * @return list<string>
     */
    public static function proceduralKeys(): array
    {
        return [
            'anunciada',
            'propuesta_formal',
            'en_tramitacion',
            'aprobada_pendiente',
            'vigente',
            'en_aplicacion',
            'suspendida',
            'revertida',
        ];
    }

    /**
     * @return list<string>
     */
    public static function publicStatusKeys(): array
    {
        return [
            'en_discusion',
            'aprobado',
            'en_aplicacion',
            'revertido',
        ];
    }
}
