<?php

declare(strict_types=1);

namespace Fidry\CpuCounter\Test;

use Fidry\CpuCounter\CpuInfoFinder;
use Fidry\CpuCounter\HwFinder;
use Fidry\CpuCounter\NProcFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCounter\HwFinder
 */
final class HwFinderTest extends TestCase
{
    /**
     * @dataProvider processProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $process,
        ?int $expected
    ): void
    {
        $actual = HwFinder::countCpuCores($process);

        self::assertSame($expected, $actual);
    }

    public static function processProvider(): iterable
    {
        yield 'empty' => [
            <<<'EOF'

            EOF,
            null,
        ];

        // MyMachine™
        yield 'example from an OSX machine' => [
            <<<'EOF'
            3

            EOF,
            3,
        ];

        yield 'no processor' => [
            <<<'EOF'
            0

            EOF,
            null,
        ];
    }
}
