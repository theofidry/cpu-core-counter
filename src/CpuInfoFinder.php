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

use function count;
use function file_get_contents;
use function is_file;
use function preg_match_all;

/**
 * Find the number of CPU cores looking up at the cpuinfo file which is available
 * on Linux systems and Windows systems with a Linux sub-system.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L903-L909
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class CpuInfoFinder implements CpuCoreFinder
{
    private function __construct()
    {
    }

    /**
     * @return positive-int|null
     */
    public static function find(): ?int
    {
        // from brianium/paratest
        if (is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = file_get_contents('/proc/cpuinfo');

            if (false !== $cpuinfo) {
                preg_match_all('/^processor/m', $cpuinfo, $matches);

                return count($matches[0]);
            }
        }

        return null;
    }
}
