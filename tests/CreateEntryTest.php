<?php

namespace Huid\NginxLogParser\Tests;

use Huid\NginxLogParser\LogParser;
use Huid\NginxLogParser\SimpleLogEntry;

class CreateEntryTest extends TestCase
{
    public function testCreateFromFilepath()
    {
        $line = '172.16.16.50 - - [02/Feb/2020:17:10:04 +0800] "GET /xxx/xxxx/xxxxx?xxx=day HTTP/1.1" 200 12416 [0.347]  "http://www.baidu.com/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36" "-"
';

        $parser = LogParser::createFromFilepath('./tests/nginx.conf');
        $data = $parser->parse($line);

        $this->assertInstanceOf(SimpleLogEntry::class, $data);
    }

    public function testCreateFromContent()
    {
        $line = '172.16.16.50 - - [02/Feb/2020:17:10:04 +0800] "GET /xxx/xxxx/xxxxx?xxx=day HTTP/1.1" 200 12416 [0.347]  "http://www.baidu.com/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36" "-"
';

        $parser = LogParser::createFromContent('
$remote_addr - $remote_user [$time_local] "$request"
                      $status $body_bytes_sent [$request_time]  "$http_referer"
                      "$http_user_agent" "$http_x_forwarded_for";
');
        $data = $parser->parse($line);

        $this->assertInstanceOf(SimpleLogEntry::class, $data);
    }
}
