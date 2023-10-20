<?php

namespace App\Logging;

use DateTimeInterface;
use Monolog\Handler\SyslogUdpHandler;

class PapertrailHandler extends SyslogUdpHandler
{
    /**
     * @param int $severity
     * @param DateTimeInterface $datetime
     * @return string
     */
    protected function makeCommonSyslogHeader(int $severity, DateTimeInterface $datetime): string
    {
        $result = parent::makeCommonSyslogHeader($severity, $datetime);

        $hostname = gethostname();
        $systemName = config('logging.channels.papertrail.system_name');
        if ($hostname && $systemName && $hostname !== '-') {
            return $this->replaceHostname($hostname, $systemName, $result);
        }

        return $result;
    }

    /**
     * @param string $hostname
     * @param string $targetHostname
     * @param string $payload
     * @return string
     */
    private function replaceHostname(string $hostname, string $targetHostname, string $payload): string
    {
        return (($pos = strpos($payload, $hostname)) !== false)
            ? substr_replace($payload, $targetHostname, $pos, strlen($hostname))
            : $payload;
    }
}
