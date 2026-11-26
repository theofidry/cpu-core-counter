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

use function defined;
use function function_exists;
use function implode;
use function posix_sysconf;
use function sprintf;
use const PHP_EOL;
use const POSIX_SC_NPROCESSORS_ONLN;

/**
 * Find the number of online logical CPU cores with
 * `posix_sysconf(POSIX_SC_NPROCESSORS_ONLN)`, i.e. sysconf(3). It requires
 * PHP 8.3+ and the posix extension, which is not available on Windows.
 *
 * Unlike most other finders, it neither spawns a process nor reads a file.
 *
 * It does not account for the CPU affinity of the process with glibc or on
 * macOS: it counts every online CPU. See CpuAffinityFinder for that.
 *
 * @see https://www.php.net/manual/en/function.posix-sysconf.php
 * @see https://man7.org/linux/man-pages/man3/sysconf.3.html
 */
final class PosixSysconfFinder implements CpuCoreFinder
{
    /**
     * @var (callable(): (int|null))|null
     */
    private $sysconf;

    /**
     * @param (callable(): (int|null))|null $sysconf Gives the value of
     *                                               _SC_NPROCESSORS_ONLN or null if it cannot be queried. Uses
     *                                               posix_sysconf() by default; it is meant for testing.
     */
    public function __construct(?callable $sysconf = null)
    {
        $this->sysconf = $sysconf;
    }

    public function diagnose(): string
    {
        $value = $this->getOnlineProcessorCount();

        $lines = [
            null === $value
                ? 'The function "posix_sysconf" or the constant "POSIX_SC_NPROCESSORS_ONLN" is not available: they require PHP 8.3+ and the posix extension.'
                : sprintf(
                    'Executed "posix_sysconf(POSIX_SC_NPROCESSORS_ONLN)" and got "%d".',
                    $value
                ),
            sprintf(
                'Will return "%s".',
                self::parse($value) ?? 'null'
            ),
        ];

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        return self::parse($this->getOnlineProcessorCount());
    }

    public function toString(): string
    {
        return 'PosixSysconfFinder';
    }

    private function getOnlineProcessorCount(): ?int
    {
        if (null !== $this->sysconf) {
            return ($this->sysconf)();
        }

        // The function may also be disabled, e.g. with disable_functions.
        if (
            !function_exists('posix_sysconf')
            || !defined('POSIX_SC_NPROCESSORS_ONLN')
        ) {
            return null;
        }

        return posix_sysconf(POSIX_SC_NPROCESSORS_ONLN);
    }

    /**
     * @return positive-int|null
     */
    private static function parse(?int $value): ?int
    {
        // sysconf() reports -1 on failure.
        return null !== $value && $value > 0 ? $value : null;
    }
}
