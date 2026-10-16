<?php

return [
    'ytdlp_bin' => env('YTDLP_BIN', 'yt-dlp'),
    'ffmpeg_bin' => env('FFMPEG_BIN', 'ffmpeg'),
    'cookies_file' => env('YTDLP_COOKIES_FILE'),
    'max_seconds' => (int) env('SOCIAL_INSPECT_MAX_SECONDS', 180),
    'whisper_bin' => env('WHISPER_BIN', 'whisper-cli'),
    'whisper_model' => env('WHISPER_MODEL', 'base'),
    'whisper_model_path' => env('WHISPER_MODEL_PATH'),
    'openai_key' => env('OPENAI_API_KEY'),
    'openai_whisper_model' => env('OPENAI_WHISPER_MODEL', 'whisper-1'),
];
