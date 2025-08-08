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

        yield 'numeric value' => [
            '18.3',
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
    }
}
