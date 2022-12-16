<?php

declare(strict_types=1);

namespace Fidry\CpuCoreCounter\Executor;

use function fclose;
use function function_exists;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;

final class ProcOpenExecutor implements ProcessExecutor
{
<<<<<<<< HEAD:src/Executor/ProcOpenExecutor.php
    public function execute(string $command): ?array
========
    /**
     * @return array{string, string}|null STDOUT & STDERR tuple
     */
    public static function execute(string $command): ?array
>>>>>>>> upstream/main:src/Executor/ProcOpen.php
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $pipes = [];

        $process = @proc_open(
            $command,
            [
                ['pipe', 'rb'],
                ['pipe', 'wb'], // stdout
                ['pipe', 'wb'], // stderr
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        proc_close($process);

        return [$stdout, $stderr];
    }
}
