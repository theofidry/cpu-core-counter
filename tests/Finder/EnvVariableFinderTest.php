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
use function Safe\putenv;
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
        string $envValue,
        ?int $expected
    ): void {
        $finder = new EnvVariableFinder('CI_CPU_LIMIT');

        putenv(sprintf('CI_CPU_LIMIT=%s', $envValue));

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
    }
}
