<?php

namespace Logger;

use Exception;
use Illuminate\View\View;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

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
     * Message thread id for bot
     *
     * @var int|null
     */
    private $messageThreadId;

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
        $this->config           = $config;
        $this->botToken         = $this->getConfigValue('token');
        $this->chatId           = $this->getConfigValue('chat_id');
        $this->messageThreadId  = $this->getConfigValue('message_thread_id');

        // define variables for text message
        $this->appName = config('app.name');
        $this->appEnv  = config('app.env');
    }

    /**
     * @param array $record
     */
    public function write($record): void
    {
        if (!$this->botToken || !$this->chatId) {
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
     * @param $record
     * @return string
     */
    private function formatText($record): string
    {
        if ($template = config('telegram-logger.template')) {
            if ($record instanceof LogRecord) {
                return view($template, array_merge($record->toArray(), [
                        'appName'   => $this->appName,
                        'appEnv'    => $this->appEnv,
                        'formatted' => $record->formatted,
                    ])
                )->render();
            }

            return view($template, array_merge($record, [
                    'appName' => $this->appName,
                    'appEnv'  => $this->appEnv,
                ])
            )->render();
        }

        return sprintf("<b>%s</b> (%s)\n%s", $this->appName, $record['level_name'], $record['formatted']);
    }

    /**
     * @param string $text
     */
    private function sendMessage(string $text): void
    {
        $httpQuery = http_build_query(array_merge(
            [
                'text'               => $text,
                'chat_id'            => $this->chatId,
                'message_thread_id'  => $this->messageThreadId,
                'parse_mode'         => 'html',
            ],
            config('telegram-logger.options', [])
        ));

        $host = $this->getConfigValue('api_host');

        $url = $host . '/bot' . $this->botToken . '/sendMessage?' . $httpQuery;

        $proxy = $this->getConfigValue('proxy');

        if (!empty($proxy)) {
            $context = stream_context_create([
                'http' => [
                    'proxy' => $proxy,
                ],
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
    private function getConfigValue($key, $defaultConfigKey = null): ?string
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return config($defaultConfigKey ?: "telegram-logger.$key");
    }
}
