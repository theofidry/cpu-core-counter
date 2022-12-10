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
use Fidry\CpuCoreCounter\Finder\WmicLogicalFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\WmicLogicalFinder
 *
 * @internal
 */
final class WmicLogicalFinderTest extends ProcOpenBasedFinderTestCase
{
    public function test_it_can_describe_itself(): void
    {
        self::assertSame(
            FinderShortClassName::get($this->getFinder()),
            $this->getFinder()->toString()
        );
    }

    protected function getFinder(): ProcOpenBasedFinder
    {
        return new WmicLogicalFinder();
    }
}
