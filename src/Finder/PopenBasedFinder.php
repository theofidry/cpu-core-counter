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

use function fgets;
use function filter_var;
use function function_exists;
use function is_int;
use function is_resource;
use function pclose;
use function popen;
use function sprintf;
use function strrpos;
use function substr;
use const FILTER_VALIDATE_INT;
use const PHP_EOL;

abstract class PopenBasedFinder implements CpuCoreFinder
{
    public function diagnose(): string
    {
        if (!function_exists('popen')) {
            return 'The function "popen" is not available.';
        }

        // Redirect the STDERR to the STDOUT since popen cannot capture the
        // STDERR.
        // We could use proc_open but this would be a greater difference between
        // the command we really execute when using the finder and what we will
        // diagnose.
        $command = $this->getCommand().' 2>&1';

        $process = popen($command, 'rb');

        if (!is_resource($process)) {
            return sprintf(
                'Could not execute the function "popen" with the command "%s".',
                $command
            );
        }

        $processResult = fgets($process);
        $exitCode = pclose($process);

        return 0 === $exitCode
            ? sprintf(
                'Executed the command "%s" and got the following output:%s%s',
                $command,
                PHP_EOL,
                $processResult
            )
            : sprintf(
                'Executed the command "%s" which exited with a non-success exit code (%d) with the following output:%s%s',
                $command,
                $exitCode,
                PHP_EOL,
                $processResult
            );
    }

    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        if (!function_exists('popen')) {
            return null;
        }

        $process = popen($this->getCommand(), 'rb');

        if (!is_resource($process)) {
            return null;
        }

        $processResult = fgets($process);
        pclose($process);

        return false === $processResult
            ? null
            : self::countCpuCores($processResult);
    }

    public function toString(): string
    {
        $class = static::class;

        /** @phpstan-ignore-next-line */
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $process): ?int
    {
        $cpuCount = filter_var($process, FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }

    abstract protected function getCommand(): string;
}
