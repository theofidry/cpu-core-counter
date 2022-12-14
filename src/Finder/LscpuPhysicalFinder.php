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

/**
 * The number of physical processors.
 *
 * @see https://stackoverflow.com/a/23378780/5846754
 */
final class LscpuPhysicalFinder extends ProcOpenBasedFinder
{
    public function toString(): string
    {
        return 'LscpuPhysicalFinder';
    }

    public function getCommand(): string
    {
        return 'lscpu -p';
    }

    public static function countCpuCores(string $lscpu): ?int
    {
        $lines = explode(PHP_EOL, $lscpu);

        $actualLines = preg_grep('/^[0-9]+\,/', $lines);

        $cores = [];
        foreach ($actualLines as $line) {
            strtok($line, ',');
            $core = strtok(',');

            if (false === $core) {
                continue;
            }

            $cores[$core] = true;
        }

        unset($cores['-']);

        return empty($cores) ? null : count($cores);
    }
}
