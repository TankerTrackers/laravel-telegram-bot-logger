<?php

declare(strict_types = 1);

namespace TankerTrackers\LaravelTelegramBotLogger;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;

final class TelegramBotHandler extends AbstractProcessingHandler
{
    private string $botApi = 'https://api.telegram.org/bot';

    /** @todo Make the $format user-customizable. */
    public function __construct(
        protected string $token,
        protected string $channel,
        protected Level $level = Level::Info
    )
    {
        $format = "%message%\n%context% %extra%";


        $formatter = new TelegramBotFormatter(true, $format);

        $this->setFormatter($formatter);

        parent::__construct($this->level);
    }

    public function sendMessage($message) : array
    {
        if (! empty($this->token) && ! empty($this->channel)) {
            try {
                $client = new Client([
                    'base_uri' => $this->botApi . $this->token . '/',
                ]);

                $client->post('sendMessage', [
                    'headers' => ['Accept' => 'application/json'],
                    'form_params' => [
                        'chat_id' => $this->channel,
                        'text' => $message,
                        'parse_mode' => 'HTML',
                    ],
                ]);

                return $this->response(true, 'Message sent!');
            } catch (Exception|GuzzleException|ClientException $exception) {
                return $this->response(false, $exception->getMessage());
            }
        } else {
            return $this->response(false, 'Unknown error. Token or chat ID likely missing or invalid.');
        }
    }

    protected function response(bool $ok, string $message) : array
    {
        return [
            'ok' => $ok,
            'message' => $message
        ];
    }

    protected function makeLogLevelPrefix($record) : string
    {
        $emoji_list = [
            Logger::DEBUG => '🚧',
            Logger::INFO => '‍🗨',
            Logger::NOTICE => '🕵',
            Logger::WARNING => '⚡️',
            Logger::ERROR => '🚨',
            Logger::CRITICAL => '🤒',
            Logger::ALERT => '👀',
            Logger::EMERGENCY => '❌❌❌',
        ];

        return $emoji_list[$record['level']] . ' ' . $record['level_name'];
    }

    /**
     * @todo Allow for more user configuration here.
     *
     * @todo The now() should be replaced with a more elegant handling of the $record['datetime'], but since it uses a
     *       custom \Monolog\DateTimeImmutable model, this felt like the easiest way to get things up and running for
     *       now.
     */
    protected function write(array $record) : void
    {
        $this->sendMessage(
            sprintf(
                '<b>%s - %s - %s</b>%s%s',
                $this->makeLogLevelPrefix($record),
                now()->toDateTimeString(),
                config('app.version'),
                PHP_EOL,
                $record['formatted'],
            ),
        );
    }
}
