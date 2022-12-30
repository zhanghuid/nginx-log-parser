<?php

declare(strict_types=1);

namespace Huid\NginxLogParser;

class FromConfigPathLogParser extends LogParser
{
    public function __construct(string $mixed, LogEntryFactoryInterface $factory = null)
    {
        parent::__construct($mixed, $factory);
    }

    /**
     * get instance from nginx conf path.
     */
    protected function parseLogFormat(string $mixed): void
    {
        if (!\is_file($mixed)) {
            throw new RuntimeException("{$mixed} not exist");
        }

        $lines = file($mixed);
        $lineNum = count($lines);
        for ($i = 0; $i < $lineNum; ++$i) {
            if (empty($lines[$i])) {
                continue;
            }
            // 过滤掉带注释的
            if (false !== strpos($lines[$i], '#')) {
                continue;
            }

            if (false === strpos($lines[$i], self::LOG_FORMAT_FLAG)) {
                continue;
            }

            $logString = str_replace("'", '', trim($lines[$i]));
            while (true) {
                ++$i;
                $logString .= str_replace("'", '', trim($lines[$i]));
                if (false !== strpos($lines[$i], ';')) {
                    break;
                }
            }
            $this->logFormatSegments[] = rtrim($logString, ';');
        }
    }
}
