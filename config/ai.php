<?php

return [
    'default' => env('AI_DEFAULT_PROVIDER', 'ollama'),

    'providers' => [
        'ollama' => [
            'driver' => 'ollama',
            'key' => env('OLLAMA_API_KEY', ''),
            'url' => env('OLLAMA_URL', 'http://100.82.100.80:11434'),
            'timeout' => env('AI_TIMEOUT', 120),
        ],

        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key' => env('GEMINI_API_KEY'),
        ],

        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key' => env('GROQ_API_KEY'),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key' => env('MISTRAL_API_KEY'),
        ],

        'deepseek' => [
            'driver' => 'deepseek',
            'key' => env('DEEPSEEK_API_KEY'),
        ],

        'xai' => [
            'driver' => 'xai',
            'key' => env('XAI_API_KEY'),
        ],
    ],

    'models' => [
        'chat' => env('AI_CHAT_MODEL', 'qwen2.5:0.5b'),
        'embedding' => env('AI_EMBEDDING_MODEL'),
        'image' => env('AI_IMAGE_MODEL'),
        'audio' => env('AI_AUDIO_MODEL'),
        'rerank' => env('AI_RERANK_MODEL'),
    ],

    'timeout' => env('AI_TIMEOUT', 120),
];
