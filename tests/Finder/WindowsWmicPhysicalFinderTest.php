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

use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;
use Fidry\CpuCoreCounter\Finder\WindowsWmicPhysicalFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\WindowsWmicPhysicalFinder
 *
 * @internal
 */
final class WindowsWmicPhysicalFinderTest extends ProcOpenBasedFinderTestCase
{
    public function test_it_can_describe_itself(): void
    {
        self::assertSame('WindowsWmicPhysicalFinder', $this->getFinder()->toString());
    }

    protected function getFinder(): ProcOpenBasedFinder
    {
        return new WindowsWmicPhysicalFinder();
    }
}
