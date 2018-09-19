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

## Create bot

For using this package you need to create Telegram bot

1. Go to @BotFather in the Telegram
2. Send ``/newbot``
3. Set up name and bot-name for your bot.
4. Get token and add it to your .env file (it is written above)
5. Go to your bot and send ``/start`` message
