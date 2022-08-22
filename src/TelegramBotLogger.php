<?php

declare(strict_types = 1);

namespace TankerTrackers\LaravelTelegramBotLogger;

use Monolog\Logger;

final class TelegramBotLogger
{
    public function __invoke(array $config) : Logger
    {
        $handler = new TelegramBotHandler(
            token: $config['token'],
            channel: $config['channel'],
            level: $config['level'] ?? Logger::INFO
        );

        return new Logger('telegram', [$handler]);
    }
}
