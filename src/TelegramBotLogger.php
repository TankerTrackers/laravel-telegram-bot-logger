<?php

declare(strict_types = 1);

namespace TankerTrackers\LaravelTelegramBotLogger;

use Monolog\Logger;

class TelegramBotLogger
{
    public function __invoke(array $config) : Logger
    {
        // In production environments, show only log messages of level INFO and greater.
        // @todo extract this to a configuration setting instead.
        $level = $config['level'];

        $handler = new TelegramBotHandler($config['token'], $config['channel'], $level);

        return new Logger('log', [$handler]);
    }
}
