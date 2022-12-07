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

namespace Fidry\CpuCoreCounter\Test;

use Fidry\CpuCoreCounter\HwPhysicalFinder;
use Fidry\CpuCoreCounter\PopenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\HwPhysicalFinder
 *
 * @internal
 */
final class HwPhysicalFinderTest extends PopenBasedFinderTestCase
{
    protected function getFinder(): PopenBasedFinder
    {
        return new HwPhysicalFinder();
    }
}
