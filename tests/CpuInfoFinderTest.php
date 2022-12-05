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

namespace Fidry\CpuCoreCounter\Test;

use Fidry\CpuCoreCounter\CpuInfoFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\CpuInfoFinder
 *
 * @internal
 */
final class CpuInfoFinderTest extends TestCase
{
    /**
     * @dataProvider cpuInfoProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $cpuInfo,
        ?int $expected
    ): void {
        $actual = CpuInfoFinder::countCpuCores($cpuInfo);

        self::assertSame($expected, $actual);
    }

    public static function cpuInfoProvider(): iterable
    {
        yield 'empty' => [
            <<<'EOF'

                EOF,
            null,
        ];

        yield 'whitespace' => [
            <<<'EOF'

                EOF,
            null,
        ];

        // $ docker run  --tty --rm --platform linux/amd64 alpine:3.14 cat /proc/cpuinfo
        yield 'example from an alpine Docker image' => [
            <<<'EOF'
                processor	: 0
                BogoMIPS	: 48.00
                Features	: fp asimd evtstrm aes pmull sha1 sha2 crc32 atomics fphp asimdhp cpuid asimdrdm jscvt fcma lrcpc dcpop sha3 asimddp sha512 asimdfhm dit uscat ilrcpc flagm sb paca pacg dcpodp flagm2 frint
                CPU implementer	: 0x00
                CPU architecture: 8
                CPU variant	: 0x0
                CPU part	: 0x000
                CPU revision	: 0

                processor	: 1
                BogoMIPS	: 48.00
                Features	: fp asimd evtstrm aes pmull sha1 sha2 crc32 atomics fphp asimdhp cpuid asimdrdm jscvt fcma lrcpc dcpop sha3 asimddp sha512 asimdfhm dit uscat ilrcpc flagm sb paca pacg dcpodp flagm2 frint
                CPU implementer	: 0x00
                CPU architecture: 8
                CPU variant	: 0x0
                CPU part	: 0x000
                CPU revision	: 0

                processor	: 2
                BogoMIPS	: 48.00
                Features	: fp asimd evtstrm aes pmull sha1 sha2 crc32 atomics fphp asimdhp cpuid asimdrdm jscvt fcma lrcpc dcpop sha3 asimddp sha512 asimdfhm dit uscat ilrcpc flagm sb paca pacg dcpodp flagm2 frint
                CPU implementer	: 0x00
                CPU architecture: 8
                CPU variant	: 0x0
                CPU part	: 0x000
                CPU revision	: 0

                EOF,
            3,
        ];
    }
}
