# nginx-log-parser

## 安装
```bash
composer require huid/nginx-log-parser:~2.0
```

## 使用
1. 使用 nginx.conf 文件
```php

$parser = new \Huid\NginxLogParser\LogParser::createFromFilepath('your-nginx-conf-path/nginx.conf');
$lines = file('/var/log/nginx/access.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $data = $parser->parse($line);
}
```

2. 使用 nginx.conf log_format 内容
```php
$parser = new \Huid\NginxLogParser\LogParser::createFromContent('
$remote_addr - $remote_user [$time_local] "$request"
                      $status $body_bytes_sent [$request_time]  "$http_referer"
                      "$http_user_agent" "$http_x_forwarded_for";
');
$lines = file('/var/log/nginx/access.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $data = $parser->parse($line);
}
```

## 自定义

### 多段 log_format 的情况
1. nginx conf
```nginx
http {

    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent [$request_time]  "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    log_format  err  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent [$request_time]  "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';
		      #'resp_body:"$resp_body" resp_header:"$resp_header"';
}
```

2. 获取指定 log_format
```php
$parser = new \Huid\NginxLogParser\LogParser('your-nginx-conf-path/nginx.conf');
$parser->setName('err');
```

### 使用自定义的字段解析器
1. 定义工厂类
```php
class MyEntry implements Huid\NginxLogParser\LogEntryInterface
{
}

class MyEntryFactory implements \Huid\NginxLogParser\LogEntryFactoryInterface
{
    public function create(array $data): \Huid\NginxLogParser\LogEntryInterface
    {
        // @TODO implement your code here to return a instance of MyEntry
    }
}
```

2. 使用自定义的工厂类
```php
$factory = new MyEntryFactory();
$parser = new Huid\NginxLogParser\LogParser('your-nginx-conf-path/nginx.conf', $factory);
$entry = $parser->parse('172.16.16.50 - - [02/Feb/2020:17:10:04 +0800] "GET /xxx/xxxx/xxxxx?xxx=day HTTP/1.1" 200 12416 [0.347]  "http://www.baidu.com/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36" "-"
');
```

## 参考
[kassner/log-parser](https://github.com/kassner/log-parser)