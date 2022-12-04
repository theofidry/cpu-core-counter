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
use function is_resource;
use function pclose;
use function popen;

/**
 * Find the number of CPU cores for Windows.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L912-L916
 */
final class WindowsWmicFinder implements CpuCoreFinder
{
    private function __construct()
    {
    }

    /**
     * @return positive-int|null
     */
    public static function find(): ?int
    {
        // Windows
        $process = popen('wmic cpu get NumberOfLogicalProcessors', 'rb');

        if (is_resource($process)) {
            fgets($process);
            $cores = (int) fgets($process);
            pclose($process);

            return $cores;
        }

        return null;
    }
}
