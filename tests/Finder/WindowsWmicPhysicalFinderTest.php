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

use Fidry\CpuCoreCounter\Finder\PopenBasedFinder;
use Fidry\CpuCoreCounter\Finder\WindowsWmicPhysicalFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\WindowsWmicPhysicalFinder
 *
 * @internal
 */
final class WindowsWmicPhysicalFinderTest extends PopenBasedFinderTestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new WindowsWmicPhysicalFinder();

        self::assertSame('WindowsWmicPhysicalFinder', $finder->toString());
    }

    protected function getFinder(): PopenBasedFinder
    {
        return new WindowsWmicPhysicalFinder();
    }
}
