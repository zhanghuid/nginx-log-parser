<?php

declare(strict_types=1);

namespace Huid\NginxLogParser;

class SimpleLogEntry implements LogEntryInterface
{
    /** @var int|null */
    public $local_time_stamp = 0;
}
