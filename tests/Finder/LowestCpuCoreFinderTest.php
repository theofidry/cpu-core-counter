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
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\LowestCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use PHPUnit\Framework\TestCase;
use function implode;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\LowestCpuCoreFinder
 *
 * @internal
 */
final class LowestCpuCoreFinderTest extends TestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new LowestCpuCoreFinder(
            new DummyCpuCoreFinder(2),
            new NullCpuCoreFinder()
        );

        self::assertSame(
            'LowestCpuCoreFinder(DummyCpuCoreFinder(value=2),NullCpuCoreFinder)',
            $finder->toString()
        );
    }

    /**
     * @dataProvider finderProvider
     *
     * @param list<CpuCoreFinder> $finders
     */
    public function test_it_finds_the_lowest_result(array $finders, ?int $expected): void
    {
        $finder = new LowestCpuCoreFinder(...$finders);

        self::assertSame($expected, $finder->find());
    }

    public static function finderProvider(): iterable
    {
        yield 'no finder' => [
            [],
            null,
        ];

        yield 'no finder finds a result' => [
            [
                new NullCpuCoreFinder(),
                new NullCpuCoreFinder(),
                ],
            null,
        ];

        yield 'one finder finds a result' => [
            [
                new NullCpuCoreFinder(),
                new DummyCpuCoreFinder(3),
            ],
            3,
        ];

        yield 'several finders find a result' => [
            [
                new DummyCpuCoreFinder(4),
                new NullCpuCoreFinder(),
                new DummyCpuCoreFinder(2),
                new DummyCpuCoreFinder(3),
            ],
            2,
        ];
    }

    public function test_it_can_diagnose_its_finders(): void
    {
        $finder = new LowestCpuCoreFinder(
            new DummyCpuCoreFinder(4),
            new DummyCpuCoreFinder(2)
        );

        $expected = implode(
            PHP_EOL,
            [
                'DummyCpuCoreFinder(value=4):',
                (new DummyCpuCoreFinder(4))->diagnose(),
                'DummyCpuCoreFinder(value=2):',
                (new DummyCpuCoreFinder(2))->diagnose(),
                'Will return "2".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }
}
