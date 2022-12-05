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

namespace Fidry\CpuCoreCounter;

use Fidry\CpuCoreCounter\Exec\ExecException;
use Fidry\CpuCoreCounter\Exec\ShellExec;
use function filter_var;
use function function_exists;
use function is_int;
use function trim;
use const FILTER_VALIDATE_INT;

/**
 * @see https://github.com/infection/infection/blob/fbd8c44/src/Resource/Processor/CpuCoresCountProvider.php#L69-L82
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class NProcFinder implements CpuCoreFinder
{
    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        if (!self::supportsNproc()) {
            return null;
        }

        try {
            $nproc = ShellExec::execute('nproc --all');
        } catch (ExecException $nprocFailed) {
            return null;
        }

        return self::countCpuCores($nproc);
    }

    private static function supportsNproc(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        try {
            $commandNproc = ShellExec::execute('command -v nproc');
        } catch (ExecException $noNprocCommand) {
            return false;
        }

        return '' !== trim($commandNproc);
    }

    /**
     * @return positive-int|null
     */
    public static function countCpuCores(string $nproc): ?int
    {
        $cpuCount = filter_var($nproc, FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
}
