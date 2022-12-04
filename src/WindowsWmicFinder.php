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

use function defined;
use function fgets;
use function filter_var;
use function function_exists;
use function is_int;
use function is_resource;
use function pclose;
use function popen;
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
        if (!function_exists('popen')
            || !defined('PHP_WINDOWS_VERSION_MAJOR')
        ) {
            return null;
        }

        // -n to show only the variable value
        $process = popen('wmic cpu get NumberOfLogicalProcessors', 'rb');

        if (!is_resource($process)) {
            return null;
        }

        $processResult = fgets($process);
        pclose($process);

        return false === $processResult
            ? null
            : self::countCpuCores($processResult);
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $process): ?int
    {
        $cpuCount = filter_var($process, FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
}
