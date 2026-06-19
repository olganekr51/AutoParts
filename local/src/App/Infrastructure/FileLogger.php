<?php

namespace App\Infrastructure;

use JsonException;
use Psr\Log\AbstractLogger;
use Stringable;

class FileLogger extends AbstractLogger
{
    public function __construct(private readonly string $logPath)
    {
    }

    /**
     * @throws JsonException
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $datetime = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode(
                $context,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
            ) : '';
        $logLine = "[{$datetime}] [{$level}]: {$message}{$contextString}\n";

        file_put_contents($this->logPath, $logLine, FILE_APPEND);
    }
}