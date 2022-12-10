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

use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder
 *
 * @internal
 */
final class NullCpuCoreFinderTest extends TestCase
{
    public function test_it_returns_null(): void
    {
        $finder = new NullCpuCoreFinder();

        self::assertNull($finder->find());
    }

    public function test_it_can_describe_itself(): void
    {
        $finder = new NullCpuCoreFinder();

        self::assertSame(
            FinderShortClassName::get($finder),
            $finder->toString()
        );
    }
}
