<?php

declare(strict_types = 1);

namespace TankerTrackers\LaravelTelegramBotLogger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

final class TelegramBotFormatter implements FormatterInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s e';
    public const MESSAGE_FORMAT = "<b>%level_name%</b> (%channel%) [%date%]\n\n%message%\n\n%context%%extra%";

    public function __construct(
        private readonly bool   $html = true,
        private readonly string $format = self::MESSAGE_FORMAT,
        private string          $dateFormat = self::DATE_FORMAT,
        private readonly string $separator = '-')
    {
        //
    }

    public function format(array $record) : string
    {
        $message = $this->format;

        $line_formatter = new LineFormatter();

        // === Processing the 'message' portion ===
        //
        // Here, we replace the %message% placeholder with the message being passed inside the record, making sure to
        // have replaced the "less than" and "greater than" signs first, as these can conflict with handling further
        // down the line.
        $message = str_replace(
            '%message%',
            str($record['message'])
                ->replace('<', '&lt;')
                ->replace('>', '&gt;')
                ->toString(),
            $message
        );

        // === Processing the 'context' portion ===
        //
        // We take the LineFormatter stringify() output and massage it slightly, adding spaces after the commas that
        // separate individual tags in the JSON, then adding a HTML "<pre>" wrapper around it.
        if ($record['context']) {
            $message = str_replace(
                '%context%',
                str($line_formatter->stringify($record['context']))
                    ->replace(',"', ', "')
                    ->prepend('<pre>')
                    ->append('</pre>')
                    ->append(PHP_EOL)
                    ->toString(),
                $message
            );
        } else {
            $message = str_replace('%context%', '', $message);
        }

        // === Processing the 'extra' portion ===
        //
        // Not much to do here other than just update a bit of formatting and details.
        if ($record['extra']) {
            $message = str_replace(
                '%extra%',
                str($line_formatter->stringify($record['extra']))
                    ->prepend('<b>Extra:</b> ')
                    ->append(PHP_EOL)
                    ->toString(),
                $message
            );
        } else {
            $message = str_replace('%extra%', '', $message);
        }

        $message = str_replace([
            '%level_name%',
            '%channel%',
            '%date%'
        ], [
            $record['level_name'],
            $record['channel'],
            $record['datetime']->format($this->dateFormat),
        ],
            $message
        );

        if ($this->html === false) {
            $message = strip_tags($message);
        }

        return $message;
    }

    public function formatBatch(array $records) : string
    {
        $message = '';

        foreach ($records as $record) {
            if (! empty($message)) {
                $message .= str_repeat($this->separator, 15) . PHP_EOL;
            }

            $message .= $this->format($record);
        }

        return $message;
    }
}
