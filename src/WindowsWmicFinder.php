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

namespace Fidry\CpuCounter;

use function fgets;
use function filter_var;
use function is_int;
use function is_resource;
use function pclose;
use function popen;
use function trim;
use const FILTER_VALIDATE_INT;

/**
 * Find the number of CPU cores for Windows.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L912-L916
 */
final class WindowsWmicFinder implements CpuCoreFinder
{
    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        // Windows
        $process = popen('wmic cpu get NumberOfLogicalProcessors', 'rb');

        if (is_resource($process)) {
            fgets($process);
            $cores = self::countCpuCores(fgets($process));
            pclose($process);

            return $cores;
        }

        return null;
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $process): ?int
    {
        $cpuCount = filter_var(trim($process), FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
}
