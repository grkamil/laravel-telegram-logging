# Laravel Telegram logger

Send logs to Telegram chat via Telegram bot

## Install

```

composer require grkamil/laravel-telegram-logging

```

Define Telegram Bot Token and chat id (users telegram id) and set as environment parameters.
Add to <b>.env</b> 

```
TELEGRAM_LOGGER_BOT_TOKEN=id:token
TELEGRAM_LOGGER_CHAT_ID=chat_id
```


Add to <b>config/logging.php</b> file new channel:

```php
'telegram' => [
    'driver' => 'custom',
    'via'    => Logger\TelegramLogger::class,
    'level'  => 'debug',
]
```

If your default log channel is a stack, you can add it to the <b>stack</b> channel like this
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single', 'telegram'],
]
```

Or you can simply change the default log channel in the .env 
```
LOG_CHANNEL=telegram
```

Publish config file
```
php artisan vendor:publish --provider "Logger\TelegramLoggerServiceProvider"
```

## Create bot

For using this package you need to create Telegram bot

1. Go to @BotFather in the Telegram
2. Send ``/newbot``
3. Set up name and bot-name for your bot.
4. Get token and add it to your .env file (it is written above)
5. Go to your bot and send ``/start`` message

## Lumen support

To make it work with Lumen, you need also run two steps:

1. Place config/telegram-logger.php file with following code:
```php
<?php

return [
    // Telegram logger bot token
    'token' => env('TELEGRAM_LOGGER_BOT_TOKEN'),

    // Telegram chat id
    'chat_id' => env('TELEGRAM_LOGGER_CHAT_ID')
];
```

2. Uncomment ```$app->withFacades();``` and configure the file ```$app->configure('telegram-logger');``` at bootstrap/app.php
3. Place default Laravel/Lumen logging file to config/logging.php (to add new channel).
