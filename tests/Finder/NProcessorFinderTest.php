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

namespace Fidry\CpuCoreCounter\Test\Finder;

use Fidry\CpuCoreCounter\Finder\NProcessorFinder;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\NProcessorFinder
 *
 * @internal
 */
final class NProcessorFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function getFinder(): ProcOpenBasedFinder
    {
        return new NProcessorFinder();
    }
}
