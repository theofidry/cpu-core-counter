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
use function fgets;
use function file_get_contents;
use function function_exists;
use function is_file;
use function is_resource;
use function pclose;
use function popen;
use function preg_match_all;
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
            $this->count = $this->findCount();
        }

        return $this->count;
    }

    /**
     * @return positive-int
     */
    private function findCount(): int
    {
        if (!function_exists('proc_open')) {
            return $this->count = 1;
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

        return 1;
    }
}
