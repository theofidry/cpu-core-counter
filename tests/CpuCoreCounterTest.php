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

namespace Fidry\CpuCoreCounter\Test;

use Exception;
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
use PHPUnit\Framework\TestCase;
use function get_class;
use function is_array;

/**
 * @covers \Fidry\CpuCoreCounter\CpuCoreCounter
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

    /**
     * @dataProvider cpuCoreFinderProvider
     *
     * @param list<CpuCoreFinder>                          $finders
     * @param array{CpuCoreFinder, positive-int}|Exception $expected
     */
    public function test_it_can_get_the_number_of_cpu_cores_based_on_the_registered_finders(
        array $finders,
        $expected
    ): void {
        $counter = new CpuCoreCounter($finders);

        if (is_array($expected)) {
            $expected = $expected[1];
            $actual = $counter->getCount();

            self::assertSame($expected, $actual);

            return;
        }

        // Sanity check
        self::assertInstanceOf(Exception::class, $expected);

        $this->expectException(get_class($expected));
        $this->expectExceptionMessage($expected->getMessage());

        $counter->getCount();
    }

    /**
     * @dataProvider cpuCoreFinderProvider
     *
     * @param list<CpuCoreFinder>                          $finders
     * @param array{CpuCoreFinder, positive-int}|Exception $expected
     */
    public function test_it_can_get_the_finder_and_number_of_cpu_cores_based_on_the_registered_finders(
        array $finders,
        $expected
    ): void {
        $counter = new CpuCoreCounter($finders);

        if (is_array($expected)) {
            [$expectedCores, $expectedFinder] = $expected;
            [$actualCores, $actualFinder] = $counter->getFinderAndCores();

            self::assertSame($expectedFinder, $actualFinder);
            self::assertSame($expectedCores, $actualCores);

            return;
        }

        // Sanity check
        self::assertInstanceOf(Exception::class, $expected);

        $this->expectException(get_class($expected));
        $this->expectExceptionMessage($expected->getMessage());

        $counter->getCount();
    }

    public static function cpuCoreFinderProvider(): iterable
    {
        $defaultException = NumberOfCpuCoreNotFound::create();

        yield 'no finder' => [
            [],
            $defaultException,
        ];

        yield 'single finder finds a value' => (static function () {
            $finder = new DummyCpuCoreFinder(3);

            return [
                [$finder],
                [$finder, 3],
            ];
        })();

        yield 'single finder does not find a value' => [
            [
                new NullCpuCoreFinder(),
            ],
            $defaultException,
        ];

        yield 'multiple finders find a value' => (static function () {
            $finder = new DummyCpuCoreFinder(3);

            return [
                [
                    $finder,
                    new DummyCpuCoreFinder(7),
                    new DummyCpuCoreFinder(11),
                ],
                [$finder, 3],
            ];
        })();

        yield 'multiple finders find a value with some not finding any' => (static function () {
            $finder = new DummyCpuCoreFinder(7);

            return [
                [
                    new NullCpuCoreFinder(),
                    $finder,
                    new NullCpuCoreFinder(),
                    new DummyCpuCoreFinder(11),
                ],
                [$finder, 7],
            ];
        })();
    }
}
