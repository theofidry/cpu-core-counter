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

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\LscpuPhysicalFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\LscpuLogicalFinder
 *
 * @internal
 */
final class LscpuPhysicalFinderTest extends TestCase
{
    /**
     * @dataProvider finderProvider
     */
    public function test_it_can_describe_itself(CpuCoreFinder $finder, string $expected): void
    {
        $actual = $finder->toString();

        self::assertSame($expected, $actual);
    }

    public static function finderProvider(): iterable
    {
        yield [
            new LscpuPhysicalFinder(),
            FinderShortClassName::get(new LscpuPhysicalFinder())
        ];
    }

    /**
     * @dataProvider lscpuProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $lscpu,
        ?int $expected
    ): void {
        $actual = LscpuPhysicalFinder::countCpuCores($lscpu);

        self::assertSame($expected, $actual);
    }

    public static function lscpuProvider(): iterable
    {
        yield 'example with four logical but two physical' => [
            <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2,L3
0,0,0,0,,0,0,0,0
1,1,0,0,,1,1,1,0
2,0,0,0,,0,0,0,0
3,1,0,0,,1,1,1,0

EOF
            ,
            2
        ];

        yield 'example with two cores' => [
            <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,0,0,0,,0,0,0
1,1,0,0,,1,1,1

EOF
            ,
            2
        ];

        yield 'example with unrecognized physical core' => [
            <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,0,0,0,,0,0,0
1,-,0,0,,1,1,1

EOF
            ,
            1
        ];

        yield 'handling lscpu failure' => [
            <<<'EOF'
lscpu: failed to determine number of CPUs: /sys/devices/system/cpu/possible: No such file or directory

EOF
            ,
            null
        ];
    }
}
