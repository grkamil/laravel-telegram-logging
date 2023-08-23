<?php

return [
    // Telegram logger bot token
    'token' => env('TELEGRAM_LOGGER_BOT_TOKEN'),

    // Telegram chat id
    'chat_id' => env('TELEGRAM_LOGGER_CHAT_ID'),

    // Telegram message thread id
    'message_thread_id' => env('TELEGRAM_LOGGER_MESSAGE_THREAD_ID', null),

    // Blade Template to use formatting logs
    'template' => env('TELEGRAM_LOGGER_TEMPLATE', 'laravel-telegram-logging::standard'),

    // Proxy server
    'proxy' => env('TELEGRAM_LOGGER_PROXY', ''),

    // Telegram API host without trailling slash
    'api_host' => env('TELEGRAM_LOGGER_API_HOST', 'https://api.telegram.org'),

    // Telegram sendMessage options: https://core.telegram.org/bots/api#sendmessage
    'options' => [
        // 'parse_mode' => 'html',
        // 'disable_web_page_preview' => true,
        // 'disable_notification' => false
    ]
];
