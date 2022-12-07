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

use Fidry\CpuCoreCounter\PopenBasedFinder;
use Fidry\CpuCoreCounter\WindowsWmicFinder;

/**
 * @covers \Fidry\CpuCoreCounter\WindowsWmicFinder
 *
 * @internal
 */
final class WindowsWmicTest extends PopenBasedFinderTestCase
{
    protected function getFinder(): PopenBasedFinder
    {
        return new WindowsWmicFinder();
    }
}
