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

use Fidry\CpuCoreCounter\Finder\CpuInfoFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CpuInfoFinder
 *
 * @internal
 */
final class CpuInfoFinderTest extends TestCase
{
    /**
     * @var CpuInfoFinder
     */
    private $finder;

    protected function setUp(): void
    {
        $this->finder = new CpuInfoFinder();
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
        yield 'empty' => [
            <<<'EOF'

EOF
            ,
            null,
        ];

        yield 'whitespace' => [
            <<<'EOF'
 
EOF
            ,
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

EOF
            ,
            3,
        ];

        yield 'example of result got on GitHub Actions for Ubuntu' => [
            <<<'EOF'
processor	: 0
vendor_id	: GenuineIntel
cpu family	: 6
model		: 106
model name	: Intel(R) Xeon(R) Platinum 8370C CPU @ 2.80GHz
stepping	: 6
microcode	: 0xffffffff
cpu MHz		: 2793.438
cache size	: 49152 KB
physical id	: 0
siblings	: 2
core id		: 0
cpu cores	: 2
apicid		: 0
initial apicid	: 0
fpu		: yes
fpu_exception	: yes
cpuid level	: 21
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ss ht syscall nx pdpe1gb rdtscp lm constant_tsc rep_good nopl xtopology cpuid pni pclmulqdq ssse3 fma cx16 pcid sse4_1 sse4_2 movbe popcnt aes xsave avx f16c rdrand hypervisor lahf_lm abm 3dnowprefetch invpcid_single pti fsgsbase bmi1 hle avx2 smep bmi2 erms invpcid rtm avx512f avx512dq rdseed adx smap clflushopt avx512cd avx512bw avx512vl xsaveopt xsavec xsaves md_clear
bugs		: cpu_meltdown spectre_v1 spectre_v2 spec_store_bypass l1tf mds swapgs taa itlb_multihit mmio_stale_data
bogomips	: 5586.87
clflush size	: 64
cache_alignment	: 64
address sizes	: 46 bits physical, 48 bits virtual
power management:

processor	: 1
vendor_id	: GenuineIntel
cpu family	: 6
model		: 106
model name	: Intel(R) Xeon(R) Platinum 8370C CPU @ 2.80GHz
stepping	: 6
microcode	: 0xffffffff
cpu MHz		: 2793.438
cache size	: 49152 KB
physical id	: 0
siblings	: 2
core id		: 1
cpu cores	: 2
apicid		: 1
initial apicid	: 1
fpu		: yes
fpu_exception	: yes
cpuid level	: 21
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ss ht syscall nx pdpe1gb rdtscp lm constant_tsc rep_good nopl xtopology cpuid pni pclmulqdq ssse3 fma cx16 pcid sse4_1 sse4_2 movbe popcnt aes xsave avx f16c rdrand hypervisor lahf_lm abm 3dnowprefetch invpcid_single pti fsgsbase bmi1 hle avx2 smep bmi2 erms invpcid rtm avx512f avx512dq rdseed adx smap clflushopt avx512cd avx512bw avx512vl xsaveopt xsavec xsaves md_clear
bugs		: cpu_meltdown spectre_v1 spectre_v2 spec_store_bypass l1tf mds swapgs taa itlb_multihit mmio_stale_data
bogomips	: 5586.87
clflush size	: 64
cache_alignment	: 64
address sizes	: 46 bits physical, 48 bits virtual
power management:

EOF
            ,
            2,
        ];
    }
}
