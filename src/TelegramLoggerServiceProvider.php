<?php

namespace Logger;

use Illuminate\Support\ServiceProvider;

/**
 * Class TelegramLoggerServiceProvider
 * @package Logger
 */
class TelegramLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/telegram-logger.php', 'telegram-logger');
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/telegram-logger.php' => config_path('telegram-logger.php')], 'config');
    }
}