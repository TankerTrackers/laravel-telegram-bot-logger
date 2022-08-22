# laravel-telegram-bot-logger

An easy-to-use wrapper around Telegram, allowing you to use it as a Monolog output channel in your Laravel 9.x
applications.

## Installation

```shell
composer require tankertrackers/laravel-telegram-bot-logger
```

## Usage

First, add the keys `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHANNEL` to your .env file:

```dotenv
TELEGRAM_BOT_TOKEN=123456789:ABCDEFGHIJ-abcdefghijklmnopqrstuv
TELEGRAM_CHANNEL=123456789
```

Then, include the following in your `config/logging.php`. Note that this will output `DEBUG` messages and higher to
Telegram when you are running your application in a non-production environment, but only `INFO` and higher when running
in production. You are free to change these settings as you want, of course.

`@todo add log level`

```php
        'telegram' => [
            'driver' => 'custom',
            'via' => \TankerTrackers\LaravelTelegramBotLogger\TelegramBotLogger::class,
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'channel' => env('TELEGRAM_CHANNEL'),
            'level' => app()->isProduction() ? \Monolog\Logger::INFO : \Monolog\Logger::DEBUG,
        ],
```

## Credits

`@todo` 

## Copyright

This software is covered by the MIT License. See the file `LICENSE` for more information.
