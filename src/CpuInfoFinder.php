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

/**
 * Find the number of CPU cores looking up at the cpuinfo file which is available
 * on Linux systems and Windows systems with a Linux sub-system.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L903-L909
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class CpuInfoFinder
{
    private const CPU_INFO_PATH = '/proc/cpuinfo';

    private function __construct()
    {
    }

    /**
     * @return positive-int|null
     */
    public static function find(): ?int
    {
        $cpuInfo = self::getCpuInfo();

        return null === $cpuInfo ? null : self::countCpuCores($cpuInfo);
    }

    private static function getCpuInfo(): ?string
    {
        if (!is_file(self::CPU_INFO_PATH)) {
            return null;
        }

        $cpuInfo = file_get_contents(self::CPU_INFO_PATH);

        return false === $cpuInfo
            ? null
            : $cpuInfo;
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $cpuInfo): ?int
    {
        preg_match_all('/^processor/m', $cpuInfo, $matches);

        $processorCount = count($matches[0]);

        return $processorCount > 0 ? $processorCount : null;
    }
}
