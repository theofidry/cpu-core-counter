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

use Fidry\CpuCoreCounter\Finder\LinuxyNProcessorFinder;
use Fidry\CpuCoreCounter\Finder\PopenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\LinuxyNProcessorFinder
 *
 * @internal
 */
final class LinuxyNProcessorFinderTest extends PopenBasedFinderTestCase
{
    public function test_it_can_describe_itself(): void
    {
        self::assertSame('LinuxyNProcessorFinder', $this->getFinder()->toString());
    }

    protected function getFinder(): PopenBasedFinder
    {
        return new LinuxyNProcessorFinder();
    }
}
