<?php

declare(strict_types=1);

namespace Fidry\CpuCounter\Test;

use Fidry\CpuCounter\CpuInfoFinder;
use Fidry\CpuCounter\NProcFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCounter\NProcFinder
 */
final class NProcFinderTest extends TestCase
{
    /**
     * @dataProvider nprocProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $nproc,
        ?int $expected
    ): void
    {
        $actual = NProcFinder::countCpuCores($nproc);

        self::assertSame($expected, $actual);
    }

    public static function nprocProvider(): iterable
    {
        yield 'empty' => [
            <<<'EOF'

            EOF,
            null,
        ];

        // $ docker run  --tty --rm --platform linux/amd64 alpine:3.14 nproc --all
        yield 'example from an alpine Docker image' => [
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
