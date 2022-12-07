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

/**
 * @covers \Fidry\CpuCoreCounter\Test\Finder\LabeledFinder
 *
 * @internal
 */
final class LabeledFinderTest extends TestCase
{
    public function test_it_decorates_a_finder_with_its_class_short_name_as_a_label(): void
    {
        $finder = new LabeledFinder(
            new DummyCpuCoreFinder(7)
        );

        self::assertSame('DummyCpuCoreFinder', $finder->getLabel());
        self::assertSame(7, $finder->find());
        self::assertSame('Will return "7".', $finder->diagnose());
    }

    public function test_it_decorates_a_finder_with_the_given_label_when_specified(): void
    {
        $finder = new LabeledFinder(
            new DummyCpuCoreFinder(7),
            'Foo'
        );

        self::assertSame('Foo', $finder->getLabel());
        self::assertSame(7, $finder->find());
        self::assertSame('Will return "7".', $finder->diagnose());
    }
}
