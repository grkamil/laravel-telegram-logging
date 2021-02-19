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
     * Blade template reference to be used by Logs
     * 
     * @string
     */
    private $template;

    /**
     * TelegramHandler constructor.
     * @param int $level
     */
    public function __construct($level)
    {
        $level = Logger::toMonologLevel($level);

        parent::__construct($level, true);

        // define variables for making Telegram request
        $this->botToken = config('telegram-logger.token');
        $this->chatId   = config('telegram-logger.chat_id');
        $this->template = config('telegram-logger.template');

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
            file_get_contents(
                'https://api.telegram.org/bot' . $this->botToken . '/sendMessage?'
                . http_build_query([
                    'text' => $this->applyTelegramMaxCharactersLimit($this->formatText($record)),
                    'chat_id' => $this->chatId,
                    'parse_mode' => 'html'
                ])
            );
        } catch (Exception $exception) {
            \Log::channel('single')->error($exception->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("%message% %context% %extra%\n");
    }

    /**
     * @param array $record
     * @return string
     */
    private function formatText(array $record): string
    {

        return view($this->template, array_merge($record,[
            'appName' => $this->appName,
            'appEnv'  => $this->appEnv,
            ])
        );
    }

    /**
     * @param string $text
     * @return string
     */
    private function applyTelegramMaxCharactersLimit(string $text): string
    {
        return mb_substr($text, 0, 4096);
    }
}