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

use Fidry\CpuCoreCounter\Finder\PosixSysconfFinder;
use PHPUnit\Framework\TestCase;
use function defined;
use function function_exists;
use function posix_sysconf;
use function sprintf;
use const PHP_EOL;
use const POSIX_SC_NPROCESSORS_ONLN;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\PosixSysconfFinder
 *
 * @internal
 */
final class PosixSysconfFinderTest extends TestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new PosixSysconfFinder();

        self::assertSame(
            FinderShortClassName::get($finder),
            $finder->toString()
        );
    }

    /**
     * @dataProvider sysconfProvider
     */
    public function test_it_tries_to_get_the_number_of_cores(
        ?int $sysconfValue,
        ?int $expected
    ): void {
        $finder = self::createFinder($sysconfValue);

        self::assertSame($expected, $finder->find());
    }

    public static function sysconfProvider(): iterable
    {
        yield 'several cores' => [
            12,
            12,
        ];

        yield 'one core' => [
            1,
            1,
        ];

        yield 'zero' => [
            0,
            null,
        ];

        yield 'failure reported by sysconf' => [
            -1,
            null,
        ];

        yield 'posix_sysconf() is not available' => [
            null,
            null,
        ];
    }

    /**
     * @dataProvider diagnosisProvider
     */
    public function test_it_can_diagnose(
        ?int $sysconfValue,
        string $expected
    ): void {
        $finder = self::createFinder($sysconfValue);

        self::assertSame($expected, $finder->diagnose());
    }

    public static function diagnosisProvider(): iterable
    {
        yield 'several cores' => [
            12,
            'Executed "posix_sysconf(POSIX_SC_NPROCESSORS_ONLN)" and got "12".'.PHP_EOL
            .'Will return "12".',
        ];

        yield 'failure reported by sysconf' => [
            -1,
            'Executed "posix_sysconf(POSIX_SC_NPROCESSORS_ONLN)" and got "-1".'.PHP_EOL
            .'Will return "null".',
        ];

        yield 'posix_sysconf() is not available' => [
            null,
            'The function "posix_sysconf" or the constant "POSIX_SC_NPROCESSORS_ONLN" is not available: they require PHP 8.3+ and the posix extension.'.PHP_EOL
            .'Will return "null".',
        ];
    }

    public function test_it_uses_posix_sysconf_by_default(): void
    {
        $finder = new PosixSysconfFinder();

        if (
            !function_exists('posix_sysconf')
            || !defined('POSIX_SC_NPROCESSORS_ONLN')
        ) {
            self::assertNull($finder->find());
            self::assertStringContainsString('is not available', $finder->diagnose());

            return;
        }

        $value = posix_sysconf(POSIX_SC_NPROCESSORS_ONLN);

        self::assertSame($value > 0 ? $value : null, $finder->find());
        self::assertStringContainsString(
            sprintf('and got "%d".', $value),
            $finder->diagnose()
        );
    }

    private static function createFinder(?int $sysconfValue): PosixSysconfFinder
    {
        return new PosixSysconfFinder(
            static function () use ($sysconfValue): ?int {
                return $sysconfValue;
            }
        );
    }
}
