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

use function function_exists;
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
            $this->count = self::findCount();
        }

        return $this->count;
    }

    /**
     * @return positive-int
     */
    private static function findCount(): int
    {
        if (!function_exists('proc_open')) {
            return 1;
        }

        /** @var list<class-string<CpuCoreFinder>> $finders */
        $finders = [
            CpuInfoFinder::class,
        ];

        if (DIRECTORY_SEPARATOR === '\\') {
            $finders[] = WindowsWmicFinder::class;
        }

        $finders[] = HwFinder::class;

        foreach ($finders as $finder) {
            $cores = $finder::find();

            if (null !== $cores) {
                return $cores;
            }
        }

        return 2;
    }
}
