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

namespace Fidry\CpuCoreCounter\Test\Finder\CpuInfoPhysicalFinder;

use Fidry\CpuCoreCounter\Finder\CpuInfoPhysicalFinder;
use Fidry\CpuCoreCounter\Test\FileReader\DummyFileReader;
use Fidry\CpuCoreCounter\Test\Finder\CpuInfoFinderTest;
use Fidry\CpuCoreCounter\Test\Finder\FinderShortClassName;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function implode;
use function iterator_to_array;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CpuInfoPhysicalFinder
 *
 * @internal
 */
final class CpuInfoPhysicalFinderTest extends TestCase
{
    /**
     * @var CpuInfoPhysicalFinder
     */
    private $finder;

    protected function setUp(): void
    {
        $this->finder = new CpuInfoPhysicalFinder();
    }

    protected function tearDown(): void
    {
        unset($this->finder);
    }

    public function test_it_can_describe_itself(): void
    {
        self::assertSame(
            FinderShortClassName::get($this->finder),
            $this->finder->toString()
        );
    }

    /**
     * @dataProvider cpuInfoFileProvider
     *
     * @param array<string, string> $files
     */
    public function test_it_finds_the_number_of_cpu_cores(
        array $files,
        ?int $expected
    ): void {
        $finder = new CpuInfoPhysicalFinder(new DummyFileReader($files));

        self::assertSame($expected, $finder->find());
    }

    public static function cpuInfoFileProvider(): iterable
    {
        yield 'no cpuinfo file' => [
            [],
            null,
        ];

        yield 'cpuinfo file without processor lines' => [
            ['/proc/cpuinfo' => 'foo'],
            null,
        ];

        yield 'cpuinfo file with processor lines' => [
            ['/proc/cpuinfo' => "processor\t: 0\nphysical id\t: 0\ncore id\t\t: 0\n\nprocessor\t: 1\nphysical id\t: 0\ncore id\t\t: 1\n"],
            2,
        ];
    }

    /**
     * @dataProvider diagnosisProvider
     *
     * @param array<string, string> $files
     */
    public function test_it_can_diagnose_the_cpu_cores_found(
        array $files,
        string $expected
    ): void {
        $finder = new CpuInfoPhysicalFinder(new DummyFileReader($files));

        self::assertSame($expected, $finder->diagnose());
    }

    public static function diagnosisProvider(): iterable
    {
        yield 'no cpuinfo file' => [
            [],
            'Could not read the file "/proc/cpuinfo".',
        ];

        yield 'cpuinfo file without processor lines' => [
            ['/proc/cpuinfo' => 'foo'],
            implode(
                PHP_EOL,
                [
                    'Found the file "/proc/cpuinfo" with the content:',
                    'foo',
                    'Will return "null".',
                ]
            ),
        ];

        yield 'cpuinfo file with processor lines' => [
            ['/proc/cpuinfo' => "processor\t: 0\nphysical id\t: 0\ncore id\t\t: 0"],
            implode(
                PHP_EOL,
                [
                    'Found the file "/proc/cpuinfo" with the content:',
                    "processor\t: 0\nphysical id\t: 0\ncore id\t\t: 0",
                    'Will return "1".',
                ]
            ),
        ];
    }

    /**
     * @dataProvider cpuInfoProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $cpuInfo,
        ?int $expected
    ): void {
        $actual = $this->finder::countCpuCores($cpuInfo);

        self::assertSame($expected, $actual);
    }

    public static function cpuInfoProvider(): iterable
    {
        $logicalCpuInfos = iterator_to_array(CpuInfoFinderTest::cpuInfoProvider());

        yield 'empty' => [
            '',
            null,
        ];

        // ARM does not print the "physical id" and "core id" lines.
        yield 'example from an alpine Docker image' => [
            $logicalCpuInfos['example from an alpine Docker image'][0],
            null,
        ];

        yield 'example of result got on GitHub Actions for Ubuntu' => [
            $logicalCpuInfos['example of result got on GitHub Actions for Ubuntu'][0],
            2,
        ];

        yield 'example from an s390x machine' => [
            $logicalCpuInfos['example from an s390x machine'][0],
            null,
        ];

        yield 'example from an s390x IBM z13 machine' => [
            $logicalCpuInfos['example from an s390x IBM z13 machine'][0],
            null,
        ];

        // 3 sockets of 2 cores each: the core IDs repeat across sockets.
        yield 'example from a QEMU/KVM VM with the kvm64 CPU model' => [
            $logicalCpuInfos['example from a QEMU/KVM VM with the kvm64 CPU model'][0],
            6,
        ];

        // Intel Core i5 M 560: 1 socket, 2 cores, 2 threads per core.
        // Source: https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/ts/lscpu/dumps/x86_64-dell_e4310.tar.gz
        yield 'example from a Dell Latitude E4310 with hyper-threading' => [
            self::readFixture('x86_64-dell_e4310'),
            2,
        ];

        // Intel Xeon X7550: 4 sockets, 8 cores each, 2 threads per core.
        // Source: https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/ts/lscpu/dumps/x86_64-64cpu.tar.gz
        yield 'example from a 4-socket machine with hyper-threading' => [
            self::readFixture('x86_64-64cpu'),
            32,
        ];

        // AMD EPYC 7451: 2 sockets, 24 cores each, 2 threads per core. The
        // core IDs have gaps (0-2, 4-6, ..., 28-30).
        // Source: https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/ts/lscpu/dumps/x86_64-epyc_7451.tar.gz
        yield 'example from a 2-socket AMD EPYC with non-contiguous core IDs' => [
            self::readFixture('x86_64-epyc_7451'),
            48,
        ];

        yield 'blocks without a processor line are ignored' => [
            <<<'EOF'
foo		: bar

processor	: 0
physical id	: 0
core id		: 0
EOF
            ,
            1,
        ];

        yield 'processor without a physical id' => [
            <<<'EOF'
processor	: 0
physical id	: 0
core id		: 0

processor	: 1
core id		: 1

EOF
            ,
            null,
        ];

        yield 'processor without a core id' => [
            <<<'EOF'
processor	: 0
physical id	: 0
core id		: 0

processor	: 1
physical id	: 0

EOF
            ,
            null,
        ];

        yield 'processor with a non-numeric core id' => [
            <<<'EOF'
processor	: 0
physical id	: 0
core id		: 0x1

EOF
            ,
            null,
        ];

        yield 'the physical and core IDs are not mixed up' => [
            <<<'EOF'
processor	: 0
physical id	: 1
core id		: 12

processor	: 1
physical id	: 11
core id		: 2

EOF
            ,
            2,
        ];
    }

    private static function readFixture(string $name): string
    {
        $contents = file_get_contents(__DIR__.'/Fixtures/cpuinfo/'.$name);
        self::assertIsString($contents);

        return $contents;
    }
}
