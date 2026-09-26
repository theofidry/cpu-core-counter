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

use Fidry\CpuCoreCounter\Finder\EnvVariableFinder;
use PHPUnit\Framework\TestCase;
use function sprintf;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\EnvVariableFinder
 *
 * @internal
 */
final class EnvVariableFinderTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('CI_CPU_LIMIT');
    }

    public function test_it_can_describe_itself(): void
    {
        $finder = new EnvVariableFinder('CI_CPU_LIMIT');

        self::assertSame(
            'getenv(CI_CPU_LIMIT)',
            $finder->toString()
        );
    }

    /**
     * @dataProvider envProvider
     */
    public function test_it_tries_to_get_the_number_of_cores(
        ?string $envValue,
        ?int $expected
    ): void {
        $finder = new EnvVariableFinder('CI_CPU_LIMIT');

        if (null !== $envValue) {
            putenv(sprintf('CI_CPU_LIMIT=%s', $envValue));
        }

        self::assertSame($expected, $finder->find());
    }

    public static function envProvider(): iterable
    {
        yield 'int value' => [
            '18',
            18,
        ];

        yield 'zero' => [
            '0',
            null,
        ];

        yield 'negative int value' => [
            '-3',
            null,
        ];

        yield 'no value' => [
            '',
            null,
        ];

        yield 'no environment variable' => [
            null,
            null,
        ];

        yield 'string value' => [
            'something',
            null,
        ];

        // Desired: 18 (18.3 CPUs = 18300m, so it should follow the millicores rule).
        yield 'numeric value' => [
            '18.3',
            null,
        ];

        // Desired: 1.
        yield 'decimal value' => [
            '1.5',
            null,
        ];

        // Desired: 1 (clamped to at least one core).
        yield 'decimal value below one' => [
            '0.5',
            null,
        ];

        yield 'decimal zero' => [
            '0.0',
            null,
        ];

        yield 'int value in string' => [
            '"something 18"',
            null,
        ];

        yield 'Kubernetes limit set using millicores' => [
            '3000m',
            3,
        ];

        yield 'Kubernetes limit set using millicores with trailing characters' => [
            '3000mA',
            null,
        ];

        yield 'Kubernetes limit set using millicores with leading characters' => [
            'A3000m',
            null,
        ];

        yield 'millicores with non integer value' => [
            '30.50m',
            null,
        ];

        yield 'Kubernetes limit rounded' => [
            '2500m',
            2,
        ];

        // Desired: 1 (clamped to at least one core). The limit is otherwise
        // silently ignored.
        yield 'Kubernetes limit below one core' => [
            '500m',
            null,
        ];

        // Desired: 1 (clamped to at least one core).
        yield 'Kubernetes limit of one millicore' => [
            '1m',
            null,
        ];

        yield 'Kubernetes limit of zero millicores' => [
            '0m',
            null,
        ];
    }

    /**
     * @dataProvider diagnosisProvider
     */
    public function test_it_can_diagnose(
        ?string $envValue,
        string $expected
    ): void {
        $finder = new EnvVariableFinder('CI_CPU_LIMIT');

        if (null !== $envValue) {
            putenv(sprintf('CI_CPU_LIMIT=%s', $envValue));
        }

        self::assertSame($expected, $finder->diagnose());
    }

    public static function diagnosisProvider(): iterable
    {
        yield 'no environment variable' => [
            null,
            'parse(getenv(CI_CPU_LIMIT)=false)=null',
        ];

        yield 'int value' => [
            '18',
            "parse(getenv(CI_CPU_LIMIT)='18')=18",
        ];

        yield 'zero' => [
            '0',
            "parse(getenv(CI_CPU_LIMIT)='0')=null",
        ];

        // Desired: "parse(getenv(CI_CPU_LIMIT)='3000m')=3", i.e. the same result as find().
        yield 'Kubernetes limit set using millicores' => [
            '3000m',
            "parse(getenv(CI_CPU_LIMIT)='3000m')=null",
        ];

        // Desired: "parse(getenv(CI_CPU_LIMIT)='500m')=1".
        yield 'Kubernetes limit below one core' => [
            '500m',
            "parse(getenv(CI_CPU_LIMIT)='500m')=null",
        ];

        // Desired: "parse(getenv(CI_CPU_LIMIT)='1.5')=1".
        yield 'decimal value' => [
            '1.5',
            "parse(getenv(CI_CPU_LIMIT)='1.5')=null",
        ];
    }
}
