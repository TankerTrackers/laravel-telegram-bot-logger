<?php

declare(strict_types = 1);

namespace App\Concerns\Logging;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

class TelegramBotFormatter implements FormatterInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s e';
    public const MESSAGE_FORMAT = "<b>%level_name%</b> (%channel%) [%date%]\n\n%message%\n\n%context%%extra%";

    private string $dateFormat;

    private string $format;

    private bool $html;

    private string $separator;

    public function __construct(bool $html = true, string $format = null, string $dateFormat = null, string $separator = '-')
    {
        $this->html = $html;
        $this->format = $format ?: self::MESSAGE_FORMAT;
        $this->dateFormat = $dateFormat ?: self::DATE_FORMAT;
        $this->separator = $separator;
    }

    public function format(array $record)
    {
        $message = $this->format;
        $line_formatter = new LineFormatter();

        $record['message'] = preg_replace('/<([^<]+)>/', '&lt;$1&gt;', $record['message']);

        $record['message'] = preg_replace('/^Stack trace:\n((^#\d.*\n?)*)$/m', "\n<b>Stack trace:</b>\n<code>$1</code>", $record['message']); // Put the stack trace inside <code></code> tags

        $message = str_replace('%message%', $record['message'], $message);

        if ($record['context']) {
            $context = '<pre>';
            $context .= $line_formatter->stringify($record['context']);
            $context .= '</pre>';

            // Add spacing before new keys in the JSON output.
            $context = str_replace(',"', ', "', $context);

            $message = str_replace('%context%', $context . "\n", $message);
        } else {
            $message = str_replace('%context%', '', $message);
        }

        if ($record['extra']) {
            $extra = '<b>Extra:</b> ';
            $extra .= $line_formatter->stringify($record['extra']);
            $message = str_replace('%extra%', $extra . "\n", $message);
        } else {
            $message = str_replace('%extra%', '', $message);
        }

        $message = str_replace(['%level_name%', '%channel%', '%date%'], [
            $record['level_name'],
            $record['channel'],
            $record['datetime']->format($this->dateFormat),
        ], $message);

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
                $message .= str_repeat($this->separator, 15) . "\n";
            }

            $message .= $this->format($record);
        }

        return $message;
    }
}
