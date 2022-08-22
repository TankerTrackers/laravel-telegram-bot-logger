<?php

declare(strict_types = 1);

namespace TankerTrackers\LaravelTelegramBotLogger;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class TelegramBotHandler extends AbstractProcessingHandler
{
    protected string $channel;

    protected string $token;

    private string $botApi = 'https://api.telegram.org/bot';

    public function __construct(string $token, string $channel, $level = Logger::INFO)
    {
        $this->token = $token;
        $this->channel = $channel;

        $format = "%message%\n%context% %extra%";

        $formatter = new TelegramBotFormatter(true, $format);

        $this->setFormatter($formatter);

        parent::__construct($level);
    }

    public function sendMessage($message) : array
    {
        if (! empty($this->token) && ! empty($this->channel)) {
            try {
                $client = new Client(['base_uri' => $this->botApi . $this->token . '/']);

                $client->post('sendMessage', [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'form_params' => [
                        'chat_id' => $this->channel,
                        'text' => $message,
                        'parse_mode' => 'HTML',
                    ],
                ]);

                return [
                    'ok' => true,
                    'message' => 'Message sent.',
                ];
            } catch (Exception|GuzzleException|ClientException $exception) {
                return [
                    'ok' => false,
                    'message' => $exception->getMessage(),
                ];
            }
        } else {
            return [
                'ok' => false,
                'message' => 'Token or Chat Id missing.',
            ];
        }
    }

    protected function makePrefix($record) : string
    {
        $emoji_list = [
            Logger::DEBUG => '🚧',
            Logger::INFO => '‍🗨',
            Logger::NOTICE => '🕵',
            Logger::WARNING => '⚡️',
            Logger::ERROR => '🚨',
            Logger::CRITICAL => '🤒',
            Logger::ALERT => '👀',
            Logger::EMERGENCY => '🤕',
        ];

        return $emoji_list[$record['level']] . ' ' . $record['level_name'];
    }

    protected function write(array $record) : void
    {
        $this->sendMessage(
            sprintf(
                '<b>%s - %s - %s</b>%s%s',
                $this->makePrefix($record),
                now()->toDateTimeString(),
                config('app.version'),
                PHP_EOL,
                $record['formatted'],
            ),
        );
    }
}
