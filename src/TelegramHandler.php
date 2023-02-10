<?php

namespace Logger;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class TelegramHandler
 * @package App\Logging
 */
class TelegramHandler extends AbstractProcessingHandler
{
    /**
     * Logger config
     * 
     * @var array
     */
    private $config;

    /**
     * Bot API token
     *
     * @var string
     */
    private $botToken;

    /**
     * Chat id for bot
     *
     * @var int
     */
    private $chatId;

    /**
     * Application name
     *
     * @string
     */
    private $appName;

    /**
     * Application environment
     *
     * @string
     */
    private $appEnv;

    /**
     * TelegramHandler constructor.
     * @param int $level
     */
    public function __construct(array $config)
    {
        $level = Logger::toMonologLevel($config['level']);

        parent::__construct($level, true);

        // define variables for making Telegram request
        $this->config = $config;
        $this->botToken = $this->getConfigValue('token');
        $this->chatId   = $this->getConfigValue('chat_id');

        // define variables for text message
        $this->appName = config('app.name');
        $this->appEnv  = config('app.env');
    }

    /**
     * @param array $record
     */
    public function write(array $record): void
    {
        if(!$this->botToken || !$this->chatId) {
            throw new \InvalidArgumentException('Bot token or chat id is not defined for Telegram logger');
        }

        // trying to make request and send notification
        try {
            $textChunks = str_split($this->formatText($record), 4096);

            foreach ($textChunks as $textChunk) {
                $this->sendMessage($textChunk);
            }
        } catch (Exception $exception) {
            \Log::channel('single')->error($exception->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("%message% %context% %extra%\n", null, false, true);
    }

    /**
     * @param array $record
     * @return string
     */
    private function formatText(array $record): string
    {
        if ($template = config('telegram-logger.template')) {
            return view($template, array_merge($record, [
                    'appName' => $this->appName,
                    'appEnv'  => $this->appEnv,
                ])
            );
        }

        return sprintf("<b>%s</b> (%s)\n%s", $this->appName, $record['level_name'], $record['formatted']);
    }

    /**
     * @param  string  $text
     */
    private function sendMessage(string $text): void
    {
        $httpQuery = http_build_query(array_merge(
            [
                'text' => $text,
                'chat_id' => $this->chatId,
                'parse_mode' => 'html',
            ],
            config('telegram-logger.options', [])
        ));

        $url = 'https://api.telegram.org/bot' . $this->botToken . '/sendMessage?' . $httpQuery;

        $proxy = $this->getConfigValue('proxy');

        if (!empty($proxy)) {
            $context = stream_context_create([
                'http' => [
                    'proxy' => $proxy,
                ]
            ]);
            file_get_contents($url, false, $context);
        } else {
            file_get_contents($url);
        }
    }

    /**
     * @param string $key
     * @param string $defaultConfigKey
     * @return string
     */
    private function getConfigValue($key, $defaultConfigKey = null): string
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        
        return config($defaultConfigKey ?: "telegram-logger.$key");
    }
}
