<?php

declare(strict_types=1);

namespace Huid\NginxLogParser;

abstract class LogParser
{
    public const DEFAULT_FORMAT = '/etc/nginx/nginx.conf';

    public const LOG_FORMAT_FLAG = 'log_format';

    /**
     * log format name
     * see https://nginx.org/en/docs/http/ngx_http_log_module.html#log_format.
     *
     * @var string
     */
    protected $name = 'main';

    /**
     * log format collect.
     *
     * @var mixed
     */
    protected $logFormatSegments = [];

    /**
     * convert format to pattern.
     *
     * @var array
     */
    protected $logFormatPatterns = [];

    /**
     * @var array
     */
    protected $patterns = [
        // 带有中括号
        '/(\[\$(\w+)\])/m',
        '/\$(\w+)/m',
    ];

    /**
     * support filepath / content.
     *
     * @var mixed
     */
    protected $mixed;

    /**
     * @var LogEntryFactoryInterface
     */
    protected $factory;

    public function __construct(string $mixed, LogEntryFactoryInterface $factory = null)
    {
        $this->mixed = $mixed;
        $this->factory = $factory ?? new SimpleLogEntryFactory();
    }

    /**
     * parse one line.
     *
     * @return \Huid\NginxLogParser\LogEntryInterface
     *
     * @throws \Huid\NginxLogParser\RuntimeException
     * @throws \Huid\NginxLogParser\FormatException
     */
    public function parse(string $line): LogEntryInterface
    {
        $this->parseLogFormat($this->mixed);
        $this->updatePatterns();

        if (empty($this->logFormatPatterns[$this->name])) {
            throw new RuntimeException('illegal log format name');
        }

        if (!\preg_match($this->logFormatPatterns[$this->name], $line, $matches)) {
            throw new FormatException($line);
        }

        return $this->factory->create($matches);
    }

    abstract protected function parseLogFormat(string $mixed): void;

    /**
     * update pattern.
     *
     * @throws \Huid\NginxLogParser\RuntimeException
     */
    private function updatePatterns(): void
    {
        foreach ($this->logFormatSegments as $tpl) {
            foreach ($this->patterns as $re) {
                $tpl = preg_replace_callback($re, function ($item) {
                    // 带有中括号的
                    if (3 === count($item)) {
                        return "\[(?P<{$item[2]}>(.+?))\]";
                    }

                    return "(?P<{$item[1]}>(.+?))";
                }, $tpl);
            }

            $pos = \strpos($tpl, '(?');
            if (empty($pos)) {
                throw new RuntimeException('illegal nginx content replace');
            }

            $fir = \substr($tpl, 0, $pos);
            $firArr = \preg_split('/[\s]+/', $fir);
            // log format name
            if (empty($firArr[1])) {
                throw new RuntimeException('illegal nginx content');
            }

            $sec = \substr($tpl, $pos);
            $this->logFormatPatterns[$firArr[1]] = \sprintf('/%s/m', $sec);
        }
    }

    /**
     * add pattern.
     */
    public function addPattern(string $pattern): void
    {
        $this->patterns[] = $pattern;
    }

    /**
     * set pattern.
     */
    public function setPattern(array $pattern): void
    {
        $this->patterns[] = $pattern;
    }

    /**
     * set name.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * set factory.
     *
     * @param \Huid\NginxLogParser\LogEntryFactoryInterface $factory
     */
    public function setFactory(LogEntryFactoryInterface $factory): void
    {
        $this->factory = $factory;
    }

    /**
     * pass nginx conf path
     * eg: /etc/nginx/nginx.conf.
     *
     * @return \Huid\NginxLogParser\LogParser
     */
    public static function createFromFilepath(string $filepath): self
    {
        return new FromConfigPathLogParser($filepath);
    }

    /**
     * pass nginx conf content
     * eg:
     * log_format main xxxx xxxx.
     *
     * @return \Huid\NginxLogParser\LogParser
     */
    public static function createFromContent(string $content): self
    {
        return new FromConfigContentLogParser($content);
    }
}
