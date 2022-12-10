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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\Test\Finder\FinderShortClassName
 *
 * @internal
 */
final class FinderShortClassNameTest extends TestCase
{
    public function test_it_can_find_a_finder_short_name(): void
    {
        $finder = new FakeFinder();

        $expected = 'FakeFinder';
        $actual = FinderShortClassName::get($finder);

        self::assertSame($expected, $actual);
    }
}
