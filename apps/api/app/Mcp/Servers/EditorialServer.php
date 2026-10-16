<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddClaimCheck;
use App\Mcp\Tools\AddLink;
use App\Mcp\Tools\AddProfileMention;
use App\Mcp\Tools\AddReaction;
use App\Mcp\Tools\AddSource;
use App\Mcp\Tools\AddUpdate;
use App\Mcp\Tools\CreateTopic;
use App\Mcp\Tools\GetTopic;
use App\Mcp\Tools\InspectSocialPost;
use App\Mcp\Tools\ListCategories;
use App\Mcp\Tools\ListProfiles;
use App\Mcp\Tools\ListTopics;
use App\Mcp\Tools\SetTopicCover;
use App\Mcp\Tools\UpdateTopic;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Resonaria')]
#[Version('0.1.0')]
#[Instructions('Carga temas publicados, reacciones de redes y hitos de seguimiento. Usa list_categories para elegir una categoría y list_topics para no repetir titulares. El título nombra el programa o derecho afectado, no «Presupuesto 2027» ni otra fórmula compartida. No uses «capítulo» (jerga DIPRES) en el titular: di presupuesto. hook, what_changes, who_affected, before_text, after_text y detail no van en prosa plana: usan **negrita**, {-pérdida} y {+aumento o continuidad}. El signo $ dentro de una marca solo abre una cifra en pesos ({-$1.896 millones}); nunca va delante de una palabra ({-menos sedes}, no {-$menos sedes}). Para enlazar otra nota del portal, como Wikipedia, escribe [[slug|texto visible]] o llama add_link (contexto, consecuencia, antecedente o relacionado). create_topic publica el tema. update_topic corrige el texto de un tema ya publicado, incluido el título, sin cambiar el slug. Por cada tema publicado, busca noticias y redes que aún no estén en la ficha: add_source para documentos y prensa, add_reaction para Instagram, TikTok, X, Threads y Reddit. add_update registra cómo evoluciona el trámite o la conversación (riesgo, reversion o incierto). add_claim_check contrasta lo que circula: parafrasea la idea, no guardes nombres ni hilos, y responde con fuente si se sostiene, no se sostiene, queda a medias o falta el dato. inspect_social_post lee un enlace público, transcribe audio temporal y no guarda el video. add_reaction acepta instagram, tiktok, x, threads y reddit. inspect_social_post solo lee instagram, tiktok y x. set_topic_cover publica la portada WebP del tema (image_base64 o image_url) con el mismo token MCP. add_profile_mention vincula el perfil al tema; si el slug no está en list_profiles, créalo con display_name y kind. Crea perfil a figuras del Estado (ejecutivo, diputado, senador, gobernador, alcalde, concejal, seremi, funcionario), dirigentes y militantes de partido, líderes sociales o gremiales, periodistas y expertos, y cualquier persona de interés público nombrada en la nota. list_profiles devuelve kind_options y creation_guide. profiles:scan-published enlaza por alias y cuentas X. El cuaderno sirve para archivar perfiles o revisar casos dudosos.')]
class EditorialServer extends Server
{
    protected array $tools = [
        ListCategories::class,
        ListTopics::class,
        GetTopic::class,
        CreateTopic::class,
        UpdateTopic::class,
        SetTopicCover::class,
        AddSource::class,
        AddReaction::class,
        AddUpdate::class,
        AddClaimCheck::class,
        AddLink::class,
        ListProfiles::class,
        AddProfileMention::class,
        InspectSocialPost::class,
    ];
}
