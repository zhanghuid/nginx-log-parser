<?php

declare(strict_types=1);

namespace Huid\NginxLogParser;

class SimpleLogEntryFactory implements LogEntryFactoryInterface
{
    public function create(array $data): LogEntryInterface
    {
        $entry = new SimpleLogEntry();

        foreach (array_filter(array_keys($data), 'is_string') as $key) {
            $entry->{$key} = $data[$key];
        }

        if (isset($entry->time_local)) {
            $stamp = strtotime($entry->time_local);
            if (false !== $stamp) {
                $entry->local_time_stamp = $stamp;
            }
        }

        return $entry;
    }
}
