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

use Exception;
use Fidry\CpuCounter\CpuCoreCounter;
use Fidry\CpuCounter\NumberOfCpuCoreNotFound;
use PHPUnit\Framework\TestCase;
use function get_class;
use function is_int;

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

    /**
     * @dataProvider cpuCoreFinderProvider
     *
     * @param list<CpuCoreCounter> $finders
     * @param int|Exception        $expected
     */
    public function test_it_can_get_the_number_of_cpu_cores_based_on_the_registered_finders(
        array $finders,
        $expected
    ): void {
        $counter = new CpuCoreCounter($finders);

        if (is_int($expected)) {
            $actual = $counter->getCount();

            self::assertSame($expected, $actual);

            return;
        }

        // Sanity check
        self::assertTrue($expected instanceof Exception);

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

        yield 'single finder finds a value' => [
            [
                new DummyCpuCoreFinder(3),
            ],
            3,
        ];

        yield 'single finder does not find a value' => [
            [
                new DummyCpuCoreFinder(null),
            ],
            $defaultException,
        ];

        yield 'multiple finders find a value' => [
            [
                new DummyCpuCoreFinder(3),
                new DummyCpuCoreFinder(7),
                new DummyCpuCoreFinder(11),
            ],
            3,
        ];

        yield 'multiple finders find a value with some not finding any' => [
            [
                new DummyCpuCoreFinder(null),
                new DummyCpuCoreFinder(7),
                new DummyCpuCoreFinder(null),
                new DummyCpuCoreFinder(11),
            ],
            7,
        ];
    }
}
