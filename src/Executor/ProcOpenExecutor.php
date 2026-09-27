<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\CpuCoreCounter\Executor;

use function fclose;
use function function_exists;
use function is_resource;
use function proc_close;
use function proc_open;
use function rewind;
use function stream_get_contents;
use function tmpfile;

final class ProcOpenExecutor implements ProcessExecutor
{
    public function execute(string $command): ?array
    {
        // Any of them may be disabled, e.g. with disable_functions.
        if (!function_exists('proc_open')
            || !function_exists('proc_close')
            || !function_exists('tmpfile')
            || !function_exists('fclose')
            || !function_exists('rewind')
            || !function_exists('stream_get_contents')
        ) {
            return null;
        }

        // Do not use a pipe for the STDERR: reading the STDOUT to the end
        // first would block forever if the command fills the STDERR pipe.
        $stderrFile = @tmpfile();

        if (false === $stderrFile) {
            return null;
        }

        $pipes = [];

        $process = @proc_open(
            $command,
            [
                ['pipe', 'rb'],
                ['pipe', 'wb'], // stdout
                $stderrFile,
            ],
            $pipes
        );
        // https://github.com/phpstan/phpstan/issues/13197
        /** @var array{resource, resource} $pipes */
        if (!is_resource($process)) {
            fclose($stderrFile);

            return null;
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);

        proc_close($process);

        rewind($stderrFile);
        $stderr = stream_get_contents($stderrFile);

        fclose($stderrFile);

        if (false === $stdout || false === $stderr) {
            return null;
        }

        return [$stdout, $stderr];
    }
}
