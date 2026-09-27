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
use Fidry\CpuCoreCounter\Test\FileReader\DummyFileReader;
use PHPUnit\Framework\TestCase;
use function implode;
use const PHP_EOL;

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

    public function test_it_finds_the_number_of_cpu_cores_from_the_cpuinfo_file(): void
    {
        $finder = new CpuInfoFinder(
            new DummyFileReader([
                '/proc/cpuinfo' => "processor\t: 0\nprocessor\t: 1\n",
            ])
        );

        self::assertSame(2, $finder->find());
    }

    public function test_it_cannot_find_the_number_of_cpu_cores_without_the_cpuinfo_file(): void
    {
        $finder = new CpuInfoFinder(new DummyFileReader([]));

        self::assertNull($finder->find());
    }

    public function test_it_can_diagnose_the_cpu_cores_found(): void
    {
        $finder = new CpuInfoFinder(
            new DummyFileReader([
                '/proc/cpuinfo' => "processor\t: 0\nprocessor\t: 1",
            ])
        );

        $expected = implode(
            PHP_EOL,
            [
                'Found the file "/proc/cpuinfo" with the content:',
                "processor\t: 0\nprocessor\t: 1",
                'Will return "2".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }

    public function test_it_can_diagnose_the_absence_of_cpu_cores(): void
    {
        $finder = new CpuInfoFinder(
            new DummyFileReader([
                '/proc/cpuinfo' => 'foo',
            ])
        );

        $expected = implode(
            PHP_EOL,
            [
                'Found the file "/proc/cpuinfo" with the content:',
                'foo',
                'Will return "null".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }

    public function test_it_can_diagnose_the_absence_of_the_cpuinfo_file(): void
    {
        $finder = new CpuInfoFinder(new DummyFileReader([]));

        self::assertSame(
            'Could not read the file "/proc/cpuinfo".',
            $finder->diagnose()
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

        // Source: https://github.com/prometheus/procfs/issues/256
        // Format: https://github.com/torvalds/linux/blob/v6.10/arch/s390/kernel/processor.c#L138-L159
        yield 'example from an s390x machine' => [
            <<<'EOF'
vendor_id       : IBM/S390
# processors    : 2
bogomips per cpu: 11061.00
features	: esan3 zarch stfle msa ldisp eimm dfp etf3eh highgprs 
processor 0: version = FF,  identification = 32C5C2,  machine = 2097
processor 1: version = FF,  identification = 32C5C2,  machine = 2097

EOF
            ,
            2,
        ];

        // Source: https://bugs.launchpad.net/checkbox-support/+bug/1709892
        yield 'example from an s390x IBM z13 machine' => [
            <<<'EOF'
vendor_id       : IBM/S390
# processors    : 4
bogomips per cpu: 3033.00
max thread id   : 0
features	: esan3 zarch stfle msa ldisp eimm dfp etf3eh highgprs sie
cache0          : level=1 type=Data scope=Private size=128K line_size=256 associativity=8
cache1          : level=1 type=Instruction scope=Private size=96K line_size=256 associativity=6
cache2          : level=2 type=Data scope=Private size=2048K line_size=256 associativity=8
cache3          : level=2 type=Instruction scope=Private size=2048K line_size=256 associativity=8
cache4          : level=3 type=Unified scope=Shared size=65536K line_size=256 associativity=16
cache5          : level=4 type=Unified scope=Shared size=491520K line_size=256 associativity=30
processor 0: version = FF,  identification = 128F67,  machine = 2964
processor 1: version = FF,  identification = 128F67,  machine = 2964
processor 2: version = FF,  identification = 128F67,  machine = 2964
processor 3: version = FF,  identification = 128F67,  machine = 2964

cpu number      : 0
cpu MHz dynamic : 5000
cpu MHz static  : 5000

cpu number      : 1
cpu MHz dynamic : 5000
cpu MHz static  : 5000

cpu number      : 2
cpu MHz dynamic : 5000
cpu MHz static  : 5000

cpu number      : 3
cpu MHz dynamic : 5000
cpu MHz static  : 5000

EOF
            ,
            4,
        ];

        // Source: https://github.com/Checkmk/checkmk/blob/6121001975f8dc57d152d80dee0c6b442d9dbfa4/tests/unit/cmk/plugins/collection/agent_based/test_inventory_lnx_cpuinfo.py#L16-L183
        // The Checkmk agent removes the whitespace around ":". It is restored
        // here as printed by https://github.com/torvalds/linux/blob/v6.10/arch/x86/kernel/cpu/proc.c
        yield 'example from a QEMU/KVM VM with the kvm64 CPU model' => [
            <<<'EOF'
processor	: 0
vendor_id	: AuthenticAMD
cpu family	: 15
model		: 6
model name	: Common KVM processor
stepping	: 1
microcode	: 0x1000065
cpu MHz		: 2799.998
cache size	: 512 KB
physical id	: 0
siblings	: 2
core id		: 0
cpu cores	: 2
apicid		: 0
initial apicid	: 0
fpu		: yes
fpu_exception	: yes
cpuid level	: 13
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx lm rep_good nopl cpuid extd_apicid tsc_known_freq pni cx16 x2apic hypervisor cmp_legacy 3dnowprefetch vmmcall
bugs		: fxsave_leak sysret_ss_attrs null_seg swapgs_fence spectre_v1 spectre_v2
bogomips	: 5599.99
TLB size	: 1024 4K pages
clflush size	: 64
cache_alignment	: 64
address sizes	: 40 bits physical, 48 bits virtual
power management:

processor	: 1
vendor_id	: AuthenticAMD
cpu family	: 15
model		: 6
model name	: Common KVM processor
stepping	: 1
microcode	: 0x1000065
cpu MHz		: 2799.998
cache size	: 512 KB
physical id	: 0
siblings	: 2
core id		: 1
cpu cores	: 2
apicid		: 1
initial apicid	: 1
fpu		: yes
fpu_exception	: yes
cpuid level	: 13
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx lm rep_good nopl cpuid extd_apicid tsc_known_freq pni cx16 x2apic hypervisor cmp_legacy 3dnowprefetch vmmcall
bugs		: fxsave_leak sysret_ss_attrs null_seg swapgs_fence spectre_v1 spectre_v2
bogomips	: 5599.99
TLB size	: 1024 4K pages
clflush size	: 64
cache_alignment	: 64
address sizes	: 40 bits physical, 48 bits virtual
power management:

processor	: 2
vendor_id	: AuthenticAMD
cpu family	: 15
model		: 6
model name	: Common KVM processor
stepping	: 1
microcode	: 0x1000065
cpu MHz		: 2799.998
cache size	: 512 KB
physical id	: 1
siblings	: 2
core id		: 0
cpu cores	: 2
apicid		: 2
initial apicid	: 2
fpu		: yes
fpu_exception	: yes
cpuid level	: 13
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx lm rep_good nopl cpuid extd_apicid tsc_known_freq pni cx16 x2apic hypervisor cmp_legacy 3dnowprefetch vmmcall
bugs		: fxsave_leak sysret_ss_attrs null_seg swapgs_fence spectre_v1 spectre_v2
bogomips	: 5599.99
TLB size	: 1024 4K pages
clflush size	: 64
cache_alignment	: 64
address sizes	: 40 bits physical, 48 bits virtual
power management:

processor	: 3
vendor_id	: AuthenticAMD
cpu family	: 15
model		: 6
model name	: Common KVM processor
stepping	: 1
microcode	: 0x1000065
cpu MHz		: 2799.998
cache size	: 512 KB
physical id	: 1
siblings	: 2
core id		: 1
cpu cores	: 2
apicid		: 3
initial apicid	: 3
fpu		: yes
fpu_exception	: yes
cpuid level	: 13
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx lm rep_good nopl cpuid extd_apicid tsc_known_freq pni cx16 x2apic hypervisor cmp_legacy 3dnowprefetch vmmcall
bugs		: fxsave_leak sysret_ss_attrs null_seg swapgs_fence spectre_v1 spectre_v2
bogomips	: 5599.99
TLB size	: 1024 4K pages
clflush size	: 64
cache_alignment	: 64
address sizes	: 40 bits physical, 48 bits virtual
power management:

processor	: 4
vendor_id	: AuthenticAMD
cpu family	: 15
model		: 6
model name	: Common KVM processor
stepping	: 1
microcode	: 0x1000065
cpu MHz		: 2799.998
cache size	: 512 KB
physical id	: 2
siblings	: 2
core id		: 0
cpu cores	: 2
apicid		: 4
initial apicid	: 4
fpu		: yes
fpu_exception	: yes
cpuid level	: 13
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx lm rep_good nopl cpuid extd_apicid tsc_known_freq pni cx16 x2apic hypervisor cmp_legacy 3dnowprefetch vmmcall
bugs		: fxsave_leak sysret_ss_attrs null_seg swapgs_fence spectre_v1 spectre_v2
bogomips	: 5599.99
TLB size	: 1024 4K pages
clflush size	: 64
cache_alignment	: 64
address sizes	: 40 bits physical, 48 bits virtual
power management:

processor	: 5
vendor_id	: AuthenticAMD
cpu family	: 15
model		: 6
model name	: Common KVM processor
stepping	: 1
microcode	: 0x1000065
cpu MHz		: 2799.998
cache size	: 512 KB
physical id	: 2
siblings	: 2
core id		: 1
cpu cores	: 2
apicid		: 5
initial apicid	: 5
fpu		: yes
fpu_exception	: yes
cpuid level	: 13
wp		: yes
flags		: fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx lm rep_good nopl cpuid extd_apicid tsc_known_freq pni cx16 x2apic hypervisor cmp_legacy 3dnowprefetch vmmcall
bugs		: fxsave_leak sysret_ss_attrs null_seg swapgs_fence spectre_v1 spectre_v2
bogomips	: 5599.99
TLB size	: 1024 4K pages
clflush size	: 64
cache_alignment	: 64
address sizes	: 40 bits physical, 48 bits virtual
power management:


EOF
            ,
            6,
        ];
    }
}
