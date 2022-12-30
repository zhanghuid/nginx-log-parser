<?php

declare(strict_types=1);

namespace Huid\NginxLogParser;

class FromConfigContentLogParser extends LogParser
{
    public function __construct(string $mixed, LogEntryFactoryInterface $factory = null)
    {
        parent::__construct($mixed, $factory);
    }

    /**
     *  get instance from nginx conf content.
     */
    protected function parseLogFormat(string $mixed): void
    {
        $lines = \explode(\PHP_EOL, $mixed);
        $body = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $body .= \sprintf(' %s', trim($line));
        }

        $this->logFormatSegments[] = \rtrim(\sprintf('log_format main %s', $body), ';');
    }
}
