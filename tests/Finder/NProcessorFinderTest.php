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
use Fidry\CpuCoreCounter\Finder\PopenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\NProcessorFinder
 *
 * @internal
 */
final class NProcessorFinderTest extends PopenBasedFinderTestCase
{
    public function test_it_can_describe_itself(): void
    {
        self::assertSame('NProcessorFinder', $this->getFinder()->toString());
    }

    protected function getFinder(): PopenBasedFinder
    {
        return new NProcessorFinder();
    }
}
