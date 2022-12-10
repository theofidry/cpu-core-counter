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

namespace Fidry\CpuCoreCounter\Finder;

use function preg_match;

/**
 * Find the number of logical CPU cores for Linux, BSD and OSX.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L903-L909
 * @see https://opensource.apple.com/source/xnu/xnu-792.2.4/libkern/libkern/sysctl.h.auto.html
 */
final class HwLogicalFinder extends ProcOpenBasedFinder
{
    private const CPU_CORE_COUNT_REGEX = '/NumberOfLogicalProcessors[\s\n]+(?<count>\d+)/';

    protected function getCommand(): string
    {
        return 'sysctl -n hw.logicalcpu';
    }

    public function toString(): string
    {
        return 'HwLogicalFinder';
    }

    public static function countCpuCores(string $process): ?int
    {
        if (0 === preg_match(self::CPU_CORE_COUNT_REGEX, $process, $matches)) {
            return parent::countCpuCores($process);
        }

        $count = $matches['count'];

        return parent::countCpuCores($count);
    }
}
