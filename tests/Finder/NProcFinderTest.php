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

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NProcFinder;
use PHPUnit\Framework\TestCase;
use function sprintf;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\NProcFinder
 *
 * @internal
 */
final class NProcFinderTest extends TestCase
{
    /**
     * @dataProvider finderProvider
     */
    public function test_it_can_describe_itself(CpuCoreFinder $finder, string $expected): void
    {
        $actual = $finder->toString();

        self::assertSame($expected, $actual);
    }

    public static function finderProvider(): iterable
    {
        yield [
            new NProcFinder(true),
            sprintf(
                '%s(all=true)',
                FinderShortClassName::get(new NProcFinder())
            )
        ];

        yield [
            new NProcFinder(false),
            sprintf(
                '%s(all=false)',
                FinderShortClassName::get(new NProcFinder())
            )
        ];
    }

    /**
     * @dataProvider \Fidry\CpuCoreCounter\Test\Finder\ProcOpenBasedFinderTestCase::processResultProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $nproc,
        ?int $expected
    ): void {
        $actual = NProcFinder::countCpuCores($nproc);

        self::assertSame($expected, $actual);
    }
}
