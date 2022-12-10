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

use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use PHPUnit\Framework\TestCase;
use function sprintf;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder
 *
 * @internal
 */
final class DummyCpuCoreFinderTest extends TestCase
{
    public function test_it_returns_the_number_of_cores_given(): void
    {
        $finder = new DummyCpuCoreFinder(5);

        self::assertSame(5, $finder->find());
    }

    public function test_it_can_describe_itself(): void
    {
        $finder = new DummyCpuCoreFinder(5);

        self::assertSame(
            sprintf(
                '%s(value=5)',
                FinderShortClassName::get($finder)
            ),
            $finder->toString()
        );
    }
}
