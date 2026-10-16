<?php

use App\Models\EditorialCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $case = EditorialCase::query()->where('slug', 'respuesta-del-estado-ante-granizos-y-lluvias-en-la-region-metropolitana')->first();

        if (! $case) {
            return;
        }

        $case->milestones()->delete();

        $entries = [
            [
                'occurred_at' => Carbon::parse('2026-10-13 08:00:00', 'America/Santiago'),
                'kind' => 'fuente',
                'label' => 'Senapred mantiene alerta preventiva para la RM por evento meteorológico.',
                'note' => 'La DMC también advertía probables tormentas eléctricas y granizos para la tarde.',
                'direction' => 'incierto',
                'source_url' => 'https://www.senapred.cl/alerta/se-declara-alerta-temprana-preventiva-para-la-region-metropolitana-por-evento-meteorologico-2026-10-02-09-41-28',
                'source_title' => 'Senapred',
            ],
            [
                'occurred_at' => Carbon::parse('2026-10-13 16:20:00', 'America/Santiago'),
                'kind' => 'redes',
                'label' => 'Usuarios y medios reportan granizos y tormenta eléctrica en varias comunas de la RM.',
                'note' => 'Registros en Las Condes, Providencia, Macul y otras comunas; testimonios en redes y prensa.',
                'direction' => 'incierto',
                'source_url' => 'https://www.biobiochile.cl/noticias/nacional/region-metropolitana/2026/10/13/videos-granizos-y-tormentas-electricas-sorprenden-a-capitalinos-en-diversos-puntos-de-la-rm.shtml',
                'source_title' => 'BioBioChile',
            ],
            [
                'occurred_at' => Carbon::parse('2026-10-13 16:54:00', 'America/Santiago'),
                'kind' => 'anuncio',
                'label' => 'Senapred declara Alerta Roja para toda la Región Metropolitana por el evento meteorológico.',
                'note' => 'La medida se difunde mientras la tormenta aún afectaba al centro y oriente de Santiago.',
                'direction' => 'incierto',
                'source_url' => 'https://www.adnradio.cl/2026/10/13/senapred-declara-alerta-roja-en-la-region-metropolitana-por-evento-meteorologico/',
                'source_title' => 'ADN Radio',
            ],
            [
                'occurred_at' => Carbon::parse('2026-10-13 18:15:00', 'America/Santiago'),
                'kind' => 'fuente',
                'label' => 'Medios reportan cortes de luz, anegamientos y cierres parciales del Metro.',
                'note' => 'Balance preliminar de prensa; no equivale a un informe oficial consolidado de daños.',
                'direction' => 'incierto',
                'source_url' => 'https://www.emol.com/noticias/Nacional/2026/10/13/1214032/colapso-granizo-rm.html',
                'source_title' => 'Emol',
            ],
            [
                'occurred_at' => Carbon::parse('2026-10-13 19:45:00', 'America/Santiago'),
                'kind' => 'anuncio',
                'label' => 'El ministro del Interior Claudio Alvarado afirma que el aparato de emergencia estaba desplegado.',
                'note' => 'Indicó que la intensidad de la lluvia debía disminuir y que se informarían decisiones de transporte.',
                'direction' => 'incierto',
                'source_url' => 'https://www.latercera.com/nacional/noticia/ministro-alvarado-ante-intensas-lluvias-en-la-region-metropolitana-esta-todo-el-aparato-de-emergencia-trabajando/',
                'source_title' => 'La Tercera',
            ],
            [
                'occurred_at' => Carbon::parse('2026-10-13 21:10:00', 'America/Santiago'),
                'kind' => 'redes',
                'label' => 'La alcaldesa de Las Condes y el gobernador Claudio Orrego intercambian versiones sobre coordinación tras el aluvión.',
                'note' => 'Disputa sobre reuniones del Cogrid y un estudio de riesgo; no resuelve por sí sola responsabilidades por los daños del día.',
                'direction' => 'incierto',
                'source_url' => 'https://www.t13.cl/noticia/politica/cruce-orrego-alcaldesa-las-condes-acusarlo-estar-ausente-emergencia-13-10-2026',
                'source_title' => 'T13',
            ],
        ];

        foreach ($entries as $index => $entry) {
            $case->milestones()->create([
                'occurred_on' => $entry['occurred_at']->toDateString(),
                'occurred_at' => $entry['occurred_at'],
                'kind' => $entry['kind'],
                'label' => $entry['label'],
                'note' => $entry['note'],
                'direction' => $entry['direction'],
                'source_url' => $entry['source_url'],
                'source_title' => $entry['source_title'],
                'procedural_status' => $case->procedural_status,
                'sort_order' => $index + 1,
            ]);
        }

        $case->update([
            'before_text' => '{+Senapred mantenía alerta preventiva para la RM y la DMC advertía tormentas eléctricas y granizos para la tarde del 13 de octubre.}',
            'after_text' => '**Ese mismo día**, con la tormenta en curso, Senapred declaró **Alerta Roja** para toda la RM; medios reportaron cortes de luz, anegamientos y alteraciones del Metro.',
            'content_version' => (int) $case->content_version + 1,
            'reviewed_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Sin reversión automática: los hitos anteriores ya no están disponibles.
    }
};
