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
            $this->count = $this->findCount();
        }

        return $this->count;
    }

    private static function

    private function findCount(): int
    {
        if (isset($this->count)) {
            return $this->count;
        }

        if (!function_exists('proc_open')) {
            return $this->count = 1;
        }

        // TODO: use Nproc over CPUInfo

        // from brianium/paratest
        if (is_file('/proc/cpuinfo')) {
            // TODO: CpuInfoFinder
        }

        // From Psalm: Hw should be fore CPUInfo

        if (DIRECTORY_SEPARATOR === '\\') {
           // TODO Windows
        }

        // TODO: Hw

        return $this->count = 2;
    }
}
