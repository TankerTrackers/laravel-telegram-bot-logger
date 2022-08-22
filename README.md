# laravel-telegram-bot-logger

> **Note** This was built under Laravel 9.x using PHP 8.1, so it has been locked to require at least those
> versions as I cannot guarantee functionality in previous versions of Laravel and/or PHP.

An easy-to-use wrapper around Telegram, allowing you to use it as a Monolog output channel in your Laravel
applications. It does not require any dependencies not already in Laravel and there are no extra `config`
files to worry about, allowing you to be up and running with this fairly quickly.

Note that this package is fairly opinionated in how it formats the output of the messages, but future
versions will most likely introduce several ways to customize the output. In the current version, it
outputs two or three rows, depending on if context is provided as the second input to your Log facade:

- Row 1: `[log level icon] [log level name] - [timestamp] - [app version]`
- Row 2: `[log message]`
- Row 3: `[context at JSON formatted string]`

Running the command `Log::warning('User failed password check.', ['user' => $user->id])` would result in
the following message being sent to Telegram:

```
⚡️ WARNING - 2022-08-22 00:06:20 - 1.1.9
User failed password check.
{"user":718}
```

## Installation

### 1 - Adding the Package

```shell
composer require tankertrackers/laravel-telegram-bot-logger
```

### 2 - Creating the Telegram Bot

This package requires you to create a Telegram bot, which will act as the sender of the log messages:

- Open a conversation with [`@BotFather`](https://t.me/BotFather) in Telegram.
- Send the message `/newbot` to begin creating a new bot.
- Set a name for your bot.
- Make a note of the `token`, as we will be using it later.
- Finally, open a private message with your bot and send a `/start` message to allow it to send messages to you.

### 3 - Updating your Logging Configuration

First, add the keys `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHANNEL` to your .env file:

```dotenv
TELEGRAM_BOT_TOKEN=123456789:ABCDEFGHIJ-abcdefghijklmnopqrstuv
TELEGRAM_CHANNEL=123456789
```

Note that `TELEGRAM_CHANNEL` can be either the ID of a user or a Telegram chat channel that you have invited the
bot to. To get your own ID, open a chat with [`jsondumpbot`](https://t.me/jsondumpbot) and send it a `/start`
command, and it will return to you your user's `id` value (under the key `message.chat.id`).

Getting the `id` for a Telegram chat channel seems to change every so often, so I recommend you Google for various
solutions to this, as any suggestion given here might no longer be valid in a few weeks' time.

Then, include the following in your `config/logging.php`. Note that this will output messages of level `DEBUG` and
higher to Telegram when you are running your application in a non-production environment, but only `INFO` and
higher when running in production. You are free to change these settings as you see fit, of course.

If the `level` key is not provided, it will default to `INFO` and higher.

```php
  'telegram' => [
      'driver' => 'custom',
      'via' => \TankerTrackers\LaravelTelegramBotLogger\TelegramBotLogger::class,
      'token' => env('TELEGRAM_BOT_TOKEN'),
      'channel' => env('TELEGRAM_CHANNEL'),
      'level' => env('APP_ENV') === 'production' ? \Monolog\Logger::INFO : \Monolog\Logger::DEBUG,
  ],
```

Once that is done, you can add `telegram` as a channel in your output stack at the top of `app/logging.php`:

```php
  'stack' => [
      'driver' => 'stack',
      'channels' => ['single', 'telegram'],
  ],
```

## Development

Development of this library is ongoing. Future versions will contain things like:

- Customizing the log output format, including:
  - Customizable DateTime formats.
  - Whether or not to include the app version.
  - Whether or not to include the log level icon.
  - Bringing the "metadata" inline with the "message" instead of posting it to a separate line.
  - Adding ability to display additional data based on the app environment.
- Add support for logging stacktraces in some way that doesn't look terrible.
- Allowing for easier setup of multiple outputs.
  - For example: Debug-messages **only** are sent to one channel, with everything else going to another channel.
- Verify compatibility for Laravel versions before 9.x.
- Verify compatibility for PHP versions before 8.1.

## Credits

No package exists in a vacuum, and with there already being plenty of Monolog wrappers around Telegram, I had
plenty of inspiration from other places when making this package. Each of these packages did some things that
I liked and other things that I didn't like, but reading them allowed me to better understand how best to write
my own package. One of these may be well suited for your requirements if you feel that this package is too
opinionated.

The packages I examined include, but are not limited to:

- [`grkamil/laravel-telegram-logging`](https://github.com/grkamil/laravel-telegram-logging)
- [`jacklul/monolog-telegram](https://github.com/jacklul/monolog-telegram)
- [`kgatan/monolog-telegram](https://github.com/kagatan/monolog-telegram)
- [`nechaienko/laravel-telegram-logging`](https://github.com/nechaienko/laravel-telegram-logging)
- [`rafaellaurindo/laravel-telegram-logging`](https://github.com/rafaellaurindo/laravel-telegram-logging)
- [`scary-layer/laravel-telegram-logging](https://github.com/scary-layer/laravel-telegram-logging)
- [`thanhtaivtt/laravel-telegram-logger`](https://github.com/thanhtaivtt/laravel-telegram-logger)

## Copyright

This software is covered by the MIT License. See the file `LICENSE` for more information.
