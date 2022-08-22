<?php

declare(strict_types = 1);

namespace TankerTrackers\LaravelTelegramBotLogger;

use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;

class TelegramBotLogger
{
    /**
     * @throws MissingExtensionException
     */
    public function __invoke(array $config) : Logger
    {
        // In production environments, show only log messages of level INFO and greater.
        $level = app()->isProduction() ? Logger::INFO : Logger::DEBUG;

        $handler = new TelegramBotHandler($config['token'], $config['channel'], $level);

        return new Logger('log', [$handler]);
    }
}
