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

use Closure;
use Exception;
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
use PHPUnit\Framework\TestCase;
use function get_class;
use function is_array;
use function sprintf;

/**
 * @covers \Fidry\CpuCoreCounter\CpuCoreCounter
 *
 * @internal
 */
final class CpuCoreCounterTest extends TestCase
{
    /**
     * @var null|Closure(): void
     */
    private $cleanupEnvironmentVariables;

    protected function tearDown(): void
    {
        $cleanupEnvironmentVariables = $this->cleanupEnvironmentVariables;

        if (null !== $cleanupEnvironmentVariables) {
            ($cleanupEnvironmentVariables)();
            $this->cleanupEnvironmentVariables = null;
        }
    }

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

    /**
     * @dataProvider availableCpuCoreProvider
     *
     * @param list<CpuCoreFinder>        $finders
     * @param array<string, string|null> $environmentVariables
     * @param positive-int               $expected
     */
    public function test_it_can_get_the_number_of_available_cpu_cores_for_parallelisation(
        array $finders,
        array $environmentVariables,
        ?int $reservedCpus,
        ?int $limit,
        ?float $maxLoadPerCore,
        ?float $systemLoadAverage,
        int $expected
    ): void {
        $this->setUpEnvironmentVariables($environmentVariables);

        $counter = new CpuCoreCounter($finders);

        // Sanity check: this is due to not being able to use named parameters.
        // If the reserved CPU is not set, then all other parameters should be
        // the default values
        if (null === $reservedCpus) {
            self::assertNull($limit);
            self::assertNull($maxLoadPerCore);
            self::assertNull($systemLoadAverage);
        }

        $actual = null === $reservedCpus
            ? $counter->getAvailableForParallelisation()
            : $counter->getAvailableForParallelisation(
                $reservedCpus,
                $limit,
                $maxLoadPerCore,
                $systemLoadAverage
            );

        self::assertSame($expected, $actual);
    }

    public static function availableCpuCoreProvider(): iterable
    {
        yield 'no finder' => [
            [],
            [],
            null,
            null,
            null,
            null,
            1,
        ];

        yield 'no finder, multiple CPUs reserved' => [
            [],
            [],
            3,
            null,
            null,
            null,
            1,
        ];

        yield 'CPU count found: kubernetes limit set and lower than the count found' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                ['KUBERNETES_CPU_LIMIT' => 2],
                null,
                null,
                null,
                null,
                2,
            ];
        })();

        yield 'CPU count found: kubernetes limit set and higher than the count found' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                ['KUBERNETES_CPU_LIMIT' => 8],
                null,
                null,
                null,
                null,
                4,
            ];
        })();

        yield 'CPU count found: kubernetes limit set and equal to the count found' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                ['KUBERNETES_CPU_LIMIT' => 5],
                null,
                null,
                null,
                null,
                4,
            ];
        })();

        yield 'CPU count found: kubernetes limit set and equal to the count found after reserved CPUs' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                ['KUBERNETES_CPU_LIMIT' => 4],
                null,
                null,
                null,
                null,
                4,
            ];
        })();

        yield 'CPU count found: kubernetes limit set and limit set' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                ['KUBERNETES_CPU_LIMIT' => 2],
                1,
                3,
                null,
                null,
                3,
            ];
        })();

        yield 'CPU count found' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                [],
                null,
                null,
                null,
                null,
                4,
            ];
        })();

        yield 'CPU count found higher than the limit passed' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                [],
                1,
                3,
                null,
                null,
                3,
            ];
        })();

        yield 'CPU count found, multiple CPUs reserved' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                [],
                2,
                null,
                null,
                null,
                3,
            ];
        })();

        yield 'CPU count found, all CPUs reserved' => (static function () {
            $finder = new DummyCpuCoreFinder(5);

            return [
                [$finder],
                [],
                5,
                null,
                null,
                null,
                1,
            ];
        })();

        yield 'CPU count found, over half the cores are used' => (static function () {
            $finder = new DummyCpuCoreFinder(11);

            return [
                [$finder],
                [],
                1,
                null,
                .9,
                6.,
                10,
            ];
        })();

        yield 'CPU count found, the CPUs are overloaded' => (static function () {
            $finder = new DummyCpuCoreFinder(11);

            return [
                [$finder],
                [],
                1,
                null,
                .9,
                9.5,
                1,
            ];
        })();

        yield 'CPU count found, the CPUs are being the limit set, but there is several CPUs available still' => (static function () {
            $finder = new DummyCpuCoreFinder(11);

            return [
                [$finder],
                [],
                1,
                null,
                .5,
                6.,
                4,
            ];
        })();

        yield 'CPU count found, the CPUs are at the limit of being overloaded' => (static function () {
            $finder = new DummyCpuCoreFinder(11);

            return [
                [$finder],
                [],
                1,
                null,
                .9,
                9.,
                10,
            ];
        })();

        yield 'CPU count found, the CPUs are overloaded but no load limit per CPU' => (static function () {
            $finder = new DummyCpuCoreFinder(11);

            return [
                [$finder],
                [],
                1,
                null,
                null,
                9.5,
                10,
            ];
        })();
    }

    /**
     * @param array<string, string|null> $environmentVariables
     */
    private function setUpEnvironmentVariables(array $environmentVariables): void
    {
        $cleanupCalls = [];

        foreach ($environmentVariables as $environmentName => $environmentValue) {
            putenv(
                sprintf(
                    '%s=%s',
                    $environmentName,
                    $environmentValue
                )
            );

            $cleanupCalls[] = static function () use ($environmentName): void {
                putenv($environmentName);
            };
        }

        $this->cleanupEnvironmentVariables = static function () use ($cleanupCalls): void {
            foreach ($cleanupCalls as $cleanupCall) {
                $cleanupCall();
            }
        };
    }
}
