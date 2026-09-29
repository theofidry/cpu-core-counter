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

use Fidry\CpuCoreCounter\Finder\CpuList;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CpuList
 *
 * @internal
 */
final class CpuListTest extends TestCase
{
    /**
     * @dataProvider cpuListProvider
     */
    public function test_it_counts_the_cpus(string $cpuList, ?int $expected): void
    {
        self::assertSame($expected, CpuList::count($cpuList));
    }

    public static function cpuListProvider(): iterable
    {
        yield 'single CPU' => ['3', 1];

        yield 'CPU 0' => ['0', 1];

        yield 'range' => ['0-3', 4];

        yield 'range of a single CPU' => ['2-2', 1];

        yield 'ranges and single CPUs' => ['0-1,4,6-9', 7];

        yield 'multi-digit CPU numbers' => ['10-19,128', 11];

        yield 'large range' => ['0-4294967295', 4294967296];

        yield 'empty list' => ['', null];

        yield 'reversed range' => ['1-0', null];

        yield 'reversed range after a valid range' => ['0-3,2-1', null];

        yield 'leading comma' => [',0-1', null];

        yield 'trailing comma' => ['0-1,', null];

        yield 'double comma' => ['0,,1', null];

        yield 'invalid item' => ['0-1,a', null];

        yield 'item with extra characters' => ['0-1x,2', null];

        yield 'item with a leading character' => ['x0-1', null];

        yield 'open range' => ['0-', null];

        yield 'range without a first CPU' => ['-1', null];

        yield 'range with too many bounds' => ['0-1-2', null];

        yield 'negative CPU' => ['-1-2', null];

        yield 'surrounding whitespace' => [' 0-1 ', null];

        yield 'trailing new line' => ["0-1\n", null];
    }

    /**
     * @dataProvider cpuListsProvider
     */
    public function test_it_counts_the_cpus_present_in_both_lists(
        string $cpuList,
        string $otherCpuList,
        ?int $expected
    ): void {
        self::assertSame($expected, CpuList::countIntersection($cpuList, $otherCpuList));
        self::assertSame($expected, CpuList::countIntersection($otherCpuList, $cpuList));
    }

    public static function cpuListsProvider(): iterable
    {
        yield 'same single CPU' => ['3', '3', 1];

        yield 'different single CPUs' => ['3', '4', null];

        yield 'same range' => ['0-3', '0-3', 4];

        yield 'single CPU inside a range' => ['2', '0-3', 1];

        yield 'range inside a range' => ['1-2', '0-3', 2];

        yield 'partially overlapping ranges' => ['0-3', '2-5', 2];

        yield 'ranges sharing a single CPU' => ['0-2', '2-4', 1];

        yield 'adjacent ranges' => ['0-1', '2-3', null];

        yield 'disjoint ranges' => ['0-1', '10-11', null];

        yield 'disjoint and overlapping ranges' => ['0-1,10-11', '10-11', 2];

        yield 'several ranges on both sides' => ['0-1,4,6-9', '1-6,9', 4];

        // Hyper-V VM with 4 CPUs and room to hot-add CPUs up to 240.
        yield 'possible and online CPUs' => ['0-239', '0-3', 4];

        yield 'large ranges' => ['0-4294967295', '4294967290-4294967299', 6];

        yield 'invalid list' => ['0-1,a', '0-3', null];

        yield 'empty list' => ['', '0-3', null];

        yield 'both lists are empty' => ['', '', null];

        yield 'reversed range' => ['1-0', '0-3', null];
    }
}
