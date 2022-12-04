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

namespace Fidry\CpuCounter\Test;

use Fidry\CpuCounter\CpuCoreCounter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCounter\CpuCoreCounter
 *
 * @internal
 */
final class CpuCoreCounterTest extends TestCase
{
    public function test_it_can_get_the_number_of_cpu_cores(): void
    {
        $counter = new CpuCoreCounter();

        self::assertGreaterThan(1, $counter->getCount());
    }
}
