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
use function fgets;
use function file_get_contents;
use function function_exists;
use function is_file;
use function is_resource;
use function pclose;
use function popen;
use function preg_match_all;
use const DIRECTORY_SEPARATOR;

final class CpuCoreCounter
{
    private int $count;

    /**
     * @return positive-int
     */
    public function getCount(): int
    {
        // Memoize result
        if (!isset($this->count)) {
            $this->count = self::findCount();
        }

        return $this->count;
    }

    /**
     * @return positive-int
     */
    private static function findCount(): int
    {
        if (!function_exists('proc_open')) {
            return 1;
        }

        // from brianium/paratest
        if (is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = file_get_contents('/proc/cpuinfo');

            if (false !== $cpuinfo) {
                preg_match_all('/^processor/m', $cpuinfo, $matches);

                return count($matches[0]);
            }
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows
            $process = popen('wmic cpu get NumberOfLogicalProcessors', 'rb');

            if (is_resource($process)) {
                fgets($process);
                $cores = (int) fgets($process);
                pclose($process);

                return $cores;
            }
        }

        $process = popen('sysctl -n hw.ncpu', 'rb');

        if (is_resource($process)) {
            // *nix (Linux, BSD and Mac)
            $cores = (int) fgets($process);
            pclose($process);

            return $cores;
        }

        return 2;
    }
}
