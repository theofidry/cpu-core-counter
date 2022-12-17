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

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder
 *
 * @internal
 */
final class ProcOpenBasedFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new DummyProcOpenBasedFinder(null, $executor);
    }

    public function test_it_can_override_its_parent_parsing(): void
    {
        $finder = new DummyProcOpenBasedFinder(
            static function (?int $value) { return $value + 1000; },
            $this->executor
        );
        $this->executor->setOutput(['8', '']);
        $expected = 8 + 1000;

        $actual = $finder->find();

        self::assertSame($expected, $actual);
    }
}
