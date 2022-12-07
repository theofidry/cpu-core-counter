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

namespace Fidry\CpuCoreCounter;

use function fgets;
use function filter_var;
use function function_exists;
use function is_int;
use function is_resource;
use function pclose;
use function popen;
use const FILTER_VALIDATE_INT;

abstract class PopenBasedFinder implements CpuCoreFinder
{
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
