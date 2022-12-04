<?php

declare(strict_types=1);

namespace Fidry\CpuCounter;

use Fidry\CpuCounter\Exec\ExecException;
use Fidry\CpuCounter\Exec\ShellExec;
use function count;
use function fgets;
use function file_get_contents;
use function filter_var;
use function is_file;
use function is_int;
use function is_resource;
use function pclose;
use function popen;
use function preg_match_all;
use function trim;
use const FILTER_VALIDATE_INT;

/**
 * Find the number of CPU cores for Linux, BSD and OSX.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L903-L909
 * @see https://opensource.apple.com/source/xnu/xnu-792.2.4/libkern/libkern/sysctl.h.auto.html
 */
final class HwFinder
{
    private function __construct()
    {
    }

    /**
     * @return positive-int|null
     */
    public static function find(): ?int
    {
        // -n to show only the variable value
        // Use hw.logicalcpu instead of deprecated hw.ncpu; see https://github.com/php/php-src/pull/5541
        $process = popen('sysctl -n hw.logicalcpu', 'rb');

        if (!is_resource($process)) {
            return null;
        }

        $cores = self::countCpuCores(fgets($process));
        pclose($process);

        return $cores;
    }

    /**
     * @return positive-int|null
     */
    public static function countCpuCores(string $process): ?int
    {
        $cpuCount = filter_var(trim($process), FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
}
