<?php

declare(strict_types=1);

/**
 * Copyright (c) 2017-2022 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/php-package-template
 */

namespace Fidry\CpuCounter;

use Webmozart\Assert\Assert;
use const DIRECTORY_SEPARATOR;

final class CpuCounter
{
    private static ?int $count = null;

    /**
     * @return positive-int
     */
    public static function getNumberOfCpuCores(): int
    {
        if (null !== self::$count) {
            return self::$count;
        }

        if (!\function_exists('proc_open')) {
            return self::$count = 1;
        }

        $count = \getenv('WEBMOZARTS_CONSOLE_PARALLELIZATION_CPU_COUNT');

        if (false !== $count) {
            Assert::numeric($count);
            Assert::positiveInteger((int) $count);

            return self::$count = (int) $count;
        }

        // from brianium/paratest
        if (\is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = \file_get_contents('/proc/cpuinfo');

            if (false !== $cpuinfo) {
                \preg_match_all('/^processor/m', $cpuinfo, $matches);

                return self::$count = \count($matches[0]);
            }
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows
            $process = \popen('wmic cpu get NumberOfLogicalProcessors', 'rb');

            if (\is_resource($process)) {
                \fgets($process);
                $cores = (int) \fgets($process);
                \pclose($process);

                return self::$count = $cores;
            }
        }

        $process = \popen('sysctl -n hw.ncpu', 'rb');

        if (\is_resource($process)) {
            // *nix (Linux, BSD and Mac)
            $cores = (int) \fgets($process);
            \pclose($process);

            return self::$count = $cores;
        }

        return self::$count = 2;
    }
}
