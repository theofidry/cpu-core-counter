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
     */
    public function test_it_can_get_the_number_of_available_cpu_cores_for_parallelisation(AvailableCpuCoresScenario $scenario): void
    {
        $this->setUpEnvironmentVariables($scenario->environmentVariables);

        $counter = new CpuCoreCounter($scenario->finders);

        $actual = $counter->getAvailableForParallelisation(
            $scenario->reservedCpus,
            $scenario->limit,
            $scenario->loadLimitPerCore,
            $scenario->systemLoadAverage
        );

        self::assertSame($scenario->expected, $actual->availableCpus);
    }

    public static function availableCpuCoreProvider(): iterable
    {
        yield 'no finder' => AvailableCpuCoresScenario::create(
            null,
            [],
            1,
            null,
            null,
            null,
            1
        );

        yield 'no finder, multiple CPUs reserved' => AvailableCpuCoresScenario::create(
            null,
            [],
            3,
            null,
            null,
            null,
            1
        );

        yield 'CPU count found: kubernetes limit set and lower than the count found' => AvailableCpuCoresScenario::create(
            5,
            ['KUBERNETES_CPU_LIMIT' => 2],
            1,
            null,
            null,
            null,
            2
        );

        yield 'CPU count found: kubernetes limit set and higher than the count found' => AvailableCpuCoresScenario::create(
            5,
            ['KUBERNETES_CPU_LIMIT' => 8],
            1,
            null,
            null,
            null,
            4
        );

        yield 'CPU count found: kubernetes limit set and equal to the count found' => AvailableCpuCoresScenario::create(
            5,
            ['KUBERNETES_CPU_LIMIT' => 5],
            1,
            null,
            null,
            null,
            4
        );

        yield 'CPU count found: kubernetes limit set and equal to the count found after reserved CPUs' => AvailableCpuCoresScenario::create(
            5,
            ['KUBERNETES_CPU_LIMIT' => 4],
            1,
            null,
            null,
            null,
            4
        );

        yield 'CPU count found: kubernetes limit set and limit set' => AvailableCpuCoresScenario::create(
            5,
            ['KUBERNETES_CPU_LIMIT' => 2],
            1,
            3,
            null,
            null,
            3
        );

        yield 'CPU count found: by default it reserves no CPU' => AvailableCpuCoresScenario::create(
            5,
            [],
            null,
            null,
            null,
            null,
            5
        );

        yield 'CPU count found higher than the limit passed' => AvailableCpuCoresScenario::create(
            5,
            [],
            1,
            3,
            null,
            null,
            3
        );

        yield 'CPU count found, multiple CPUs reserved' => AvailableCpuCoresScenario::create(
            5,
            [],
            2,
            null,
            null,
            null,
            3
        );

        yield 'CPU count found, all CPUs reserved' => AvailableCpuCoresScenario::create(
            5,
            [],
            5,
            null,
            null,
            null,
            1
        );

        yield 'CPU count found, over half the cores are used' => AvailableCpuCoresScenario::create(
            11,
            [],
            1,
            null,
            .9,
            6.,
            10
        );

        yield 'CPU count found, the CPUs are overloaded' => AvailableCpuCoresScenario::create(
            11,
            [],
            1,
            null,
            .9,
            9.5,
            1
        );

        yield 'CPU count found, the CPUs are being the limit set, but there is several CPUs available still' => AvailableCpuCoresScenario::create(
            11,
            [],
            1,
            null,
            .5,
            6.,
            4
        );

        yield 'CPU count found, the CPUs are at the limit of being overloaded' => AvailableCpuCoresScenario::create(
            11,
            [],
            1,
            null,
            .9,
            9.,
            10
        );

        yield 'CPU count found, the CPUs are overloaded but no load limit per CPU' => AvailableCpuCoresScenario::create(
            11,
            [],
            1,
            null,
            null,
            9.5,
            10
        );
    }

    /**
     * @dataProvider loadLimitPerCoreProvider
     */
    public function test_it_does_not_accept_invalid_load_limit_per_core(
        float $loadLimitPerCore,
        ?string $expectedExceptionMessage
    ): void {
        $cpuCoreCounter = new CpuCoreCounter();

        if (null !== $expectedExceptionMessage) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $cpuCoreCounter->getAvailableForParallelisation(
            1,
            null,
            $loadLimitPerCore
        );

        if (null === $expectedExceptionMessage) {
            $this->addToAssertionCount(1);
        }
    }

    public static function loadLimitPerCoreProvider(): iterable
    {
        yield 'below limit' => [
            -0.001,
            'The load limit per core must be in the range [0., 1.], got "-0.001".',
        ];

        yield 'within the limit (min)' => [
            0.,
            null,
        ];

        yield 'within the limit (max)' => [
            1.,
            null,
        ];

        yield 'above limit' => [
            1.001,
            'The load limit per core must be in the range [0., 1.], got "1.001".',
        ];
    }

    /**
     * @dataProvider systemLoadAverageProvider
     */
    public function test_it_does_not_accept_invalid_system_load_average(
        float $systemLoadAverage,
        ?string $expectedExceptionMessage
    ): void {
        $cpuCoreCounter = new CpuCoreCounter();

        if (null !== $expectedExceptionMessage) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $cpuCoreCounter->getAvailableForParallelisation(
            1,
            null,
            null,
            $systemLoadAverage
        );

        if (null === $expectedExceptionMessage) {
            $this->addToAssertionCount(1);
        }
    }

    public static function systemLoadAverageProvider(): iterable
    {
        yield 'below limit' => [
            -0.001,
            'The system load average must be a positive float, got "-0.001".',
        ];

        yield 'within the limit' => [
            0.,
            null,
        ];
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
