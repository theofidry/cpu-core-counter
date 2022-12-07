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
 * @private
 */
final class FinderRegistry
{
    /**
     * @return list<CpuCoreFinder> List of all the known finders with all their variants.
     */
    public static function getAllVariants(): array
    {
        return [
            new CpuInfoFinder(),
            new DummyCpuCoreFinder(1),
            new HwLogicalFinder(),
            new HwPhysicalFinder(),
            new NProcFinder(true),
            new NProcFinder(false),
            new NullCpuCoreFinder(),
            new WindowsWmicPhysicalFinder(),
            new WindowsWmicLogicalFinder(),
        ];
    }

    private function __construct()
    {
    }
}
