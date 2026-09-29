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

use Fidry\CpuCoreCounter\Finder\CpuAffinityFinder;
use Fidry\CpuCoreCounter\Test\FileReader\DummyFileReader;
use PHPUnit\Framework\TestCase;
use function implode;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CpuAffinityFinder
 *
 * @internal
 */
final class CpuAffinityFinderTest extends TestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new CpuAffinityFinder();

        self::assertSame(
            FinderShortClassName::get($finder),
            $finder->toString()
        );
    }

    /**
     * @dataProvider statusProvider
     *
     * @param array<string, string> $files
     */
    public function test_it_counts_the_allowed_cpus(array $files, ?int $expected): void
    {
        $finder = new CpuAffinityFinder(new DummyFileReader($files));

        self::assertSame($expected, $finder->find());
    }

    public static function statusProvider(): iterable
    {
        yield 'no status file' => [
            [],
            null,
        ];

        yield 'no Cpus_allowed_list line' => [
            self::createFiles(
                <<<EOF
Name:\tphp
Pid:\t1

EOF
            ),
            null,
        ];

        yield 'no pinning' => [
            self::createFiles(
                self::createStatus('7ff', '0-10')
            ),
            11,
        ];

        // docker run --cpuset-cpus=0-1
        yield 'range' => [
            self::createFiles(
                self::createStatus('3', '0-1')
            ),
            2,
        ];

        // taskset -c 3
        yield 'single CPU' => [
            self::createFiles(
                self::createStatus('8', '3')
            ),
            1,
        ];

        yield 'ranges and single CPUs' => [
            self::createFiles(
                self::createStatus('3d3', '0-1,4,6-9')
            ),
            7,
        ];

        yield 'range of a single CPU' => [
            self::createFiles(
                self::createStatus('4', '2-2')
            ),
            1,
        ];

        yield 'multi-digit CPU numbers' => [
            self::createFiles(
                self::createStatus('0', '10-19,128')
            ),
            11,
        ];

        yield 'last line without a trailing new line' => [
            self::createFiles(
                <<<EOF
Name:\tphp
Cpus_allowed_list:\t0-3
EOF
            ),
            4,
        ];

        yield 'Cpus_allowed_list is not the first line' => [
            self::createFiles(
                <<<EOF
Cpus_allowed:\t3
Mems_allowed_list:\t0-5
Cpus_allowed_list:\t0-1

EOF
            ),
            2,
        ];

        yield 'empty list' => [
            self::createFiles(
                <<<EOF
Cpus_allowed_list:\t
Mems_allowed_list:\t0

EOF
            ),
            null,
        ];

        yield 'reversed range' => [
            self::createFiles(
                self::createStatus('3', '1-0')
            ),
            null,
        ];

        yield 'reversed range after a valid range' => [
            self::createFiles(
                self::createStatus('f', '0-3,2-1')
            ),
            null,
        ];

        yield 'trailing comma' => [
            self::createFiles(
                self::createStatus('3', '0-1,')
            ),
            null,
        ];

        yield 'invalid item' => [
            self::createFiles(
                self::createStatus('3', '0-1,a')
            ),
            null,
        ];

        yield 'item with extra characters' => [
            self::createFiles(
                self::createStatus('3', '0-1x,2')
            ),
            null,
        ];

        yield 'item with a leading character' => [
            self::createFiles(
                self::createStatus('3', 'x0-1')
            ),
            null,
        ];

        yield 'open range' => [
            self::createFiles(
                self::createStatus('3', '0-')
            ),
            null,
        ];

        yield 'line prefix is not at the start of the line' => [
            self::createFiles(
                <<<EOF
Name:\tphp
X_Cpus_allowed_list:\t0-1

EOF
            ),
            null,
        ];
    }

    public function test_it_can_diagnose_the_allowed_cpus(): void
    {
        $finder = new CpuAffinityFinder(
            new DummyFileReader([
                '/proc/self/status' => <<<EOF
Name:\tphp
Cpus_allowed_list:\t0-1,4

EOF
                ,
            ])
        );

        $expected = implode(
            PHP_EOL,
            [
                'Found the file "/proc/self/status" with the content:',
                "Name:\tphp",
                "Cpus_allowed_list:\t0-1,4",
                'Will return "3".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }

    public function test_it_can_diagnose_the_absence_of_allowed_cpus(): void
    {
        $finder = new CpuAffinityFinder(
            new DummyFileReader([
                '/proc/self/status' => <<<EOF
Name:\tphp

EOF
                ,
            ])
        );

        $expected = implode(
            PHP_EOL,
            [
                'Found the file "/proc/self/status" with the content:',
                "Name:\tphp",
                'Will return "null".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }

    public function test_it_can_diagnose_the_absence_of_status_file(): void
    {
        $finder = new CpuAffinityFinder(new DummyFileReader([]));

        self::assertSame(
            'Could not read the file "/proc/self/status".',
            $finder->diagnose()
        );
    }

    /**
     * @return array<string, string>
     */
    private static function createFiles(string $status): array
    {
        return [
            '/proc/self/status' => $status,
        ];
    }

    private static function createStatus(string $mask, string $list): string
    {
        return <<<EOF
Name:\tphp
Pid:\t1
Cpus_allowed:\t{$mask}
Cpus_allowed_list:\t{$list}
Mems_allowed:\t00000000,00000001
Mems_allowed_list:\t0

EOF;
    }
}
