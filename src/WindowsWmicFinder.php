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
