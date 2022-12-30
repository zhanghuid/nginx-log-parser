<?php

declare(strict_types=1);

namespace Huid\NginxLogParser;

interface LogEntryFactoryInterface
{
    public function create(array $data): LogEntryInterface;
}
