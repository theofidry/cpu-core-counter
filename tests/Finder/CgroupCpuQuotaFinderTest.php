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

use Fidry\CpuCoreCounter\Finder\CgroupCpuQuotaFinder;
use Fidry\CpuCoreCounter\Test\FileReader\DummyFileReader;
use PHPUnit\Framework\TestCase;
use function implode;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CgroupCpuQuotaFinder
 *
 * @internal
 */
final class CgroupCpuQuotaFinderTest extends TestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new CgroupCpuQuotaFinder();

        self::assertSame(
            FinderShortClassName::get($finder),
            $finder->toString()
        );
    }

    /**
     * @dataProvider quotaProvider
     *
     * @param array<string, string> $files
     */
    public function test_it_finds_the_cpu_quota(array $files, ?int $expected): void
    {
        $finder = new CgroupCpuQuotaFinder(new DummyFileReader($files));

        self::assertSame($expected, $finder->find());
    }

    public static function quotaProvider(): iterable
    {
        yield 'no cgroup file' => [
            [],
            null,
        ];

        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#processes
        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#cpu-interface-files
        yield 'v2: quota on the process cgroup' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "200000 100000\n",
            ],
            2,
        ];

        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#limits
        yield 'v2: quota on a parent cgroup' => [
            [
                '/proc/self/cgroup' => "0::/foo/bar\n",
                '/sys/fs/cgroup/foo/cpu.max' => "300000 100000\n",
            ],
            3,
        ];

        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#limits
        yield 'v2: the lowest quota wins' => [
            [
                '/proc/self/cgroup' => "0::/foo/bar\n",
                '/sys/fs/cgroup/foo/cpu.max' => "100000 100000\n",
                '/sys/fs/cgroup/foo/bar/cpu.max' => "400000 100000\n",
            ],
            1,
        ];

        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#cpu-interface-files
        yield 'v2: unlimited' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "max 100000\n",
            ],
            null,
        ];

        // cpu.max only exists on non-root cgroups.
        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#cpu-interface-files
        yield 'v2: no cpu.max' => [
            [
                '/proc/self/cgroup' => "0::/\n",
            ],
            null,
        ];

        // docker run --cpus=2.5
        // See https://docs.docker.com/engine/containers/resource_constraints/#configure-the-default-cfs-scheduler
        yield 'v2: fractional quota' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "250000 100000\n",
            ],
            2,
        ];

        // docker run --cpus=0.5
        // See https://docs.docker.com/engine/containers/resource_constraints/#configure-the-default-cfs-scheduler
        yield 'v2: quota below one core' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "50000 100000\n",
            ],
            1,
        ];

        yield 'v2: period of zero' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "200000 0\n",
            ],
            null,
        ];

        yield 'v2: invalid content' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "garbage\n",
            ],
            null,
        ];

        // docker run --cpus=2 with a private cgroup namespace (the default on v2)
        // See https://docs.kernel.org/admin-guide/cgroup-v2.html#the-root-and-views
        // See https://docs.docker.com/reference/cli/dockerd/ (--default-cgroupns-mode)
        yield 'v2: container in its own cgroup namespace' => [
            [
                '/proc/self/cgroup' => "0::/\n",
                '/sys/fs/cgroup/cpu.max' => "200000 100000\n",
            ],
            2,
        ];

        // docker run --cgroupns=host --cpus=4
        // See https://docs.docker.com/reference/cli/docker/container/run/ (--cgroupns)
        yield 'v2: container in the host cgroup namespace' => [
            [
                '/proc/self/cgroup' => "0::/docker/0123abcd\n",
                '/sys/fs/cgroup/docker/0123abcd/cpu.max' => "400000 100000\n",
            ],
            4,
        ];

        // See https://docs.kernel.org/scheduler/sched-bwc.html#management
        // systemd mounts cpu and cpuacct together at /sys/fs/cgroup/cpu,cpuacct.
        // See https://github.com/systemd/systemd/blob/v239/src/core/mount-setup.c#L246-L305
        yield 'v1: quota' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/foo\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_quota_us' => "200000\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_period_us' => "100000\n",
            ],
            2,
        ];

        // systemd and runc also create /sys/fs/cgroup/cpu as a symlink to the joined hierarchy.
        // See https://github.com/systemd/systemd/blob/v239/src/core/mount-setup.c#L313-L325
        // See https://github.com/opencontainers/runc/blob/v1.1.12/libcontainer/rootfs_linux.go#L311-L320
        yield 'v1: controller mounted as cpu' => [
            [
                '/proc/self/cgroup' => "4:cpu:/foo\n",
                '/sys/fs/cgroup/cpu/foo/cpu.cfs_quota_us' => "300000\n",
                '/sys/fs/cgroup/cpu/foo/cpu.cfs_period_us' => "100000\n",
            ],
            3,
        ];

        // See https://docs.kernel.org/scheduler/sched-bwc.html#management
        yield 'v1: unlimited' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/foo\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_quota_us' => "-1\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_period_us' => "100000\n",
            ],
            null,
        ];

        yield 'v1: no period' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/foo\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_quota_us' => "200000\n",
            ],
            null,
        ];

        // See https://man7.org/linux/man-pages/man7/cgroups.7.html (/proc/pid/cgroup)
        yield 'v1: cpuset and cpuacct are not the cpu controller' => [
            [
                '/proc/self/cgroup' => "7:cpuset:/other\n6:cpuacct:/other\n",
                '/sys/fs/cgroup/cpu/other/cpu.cfs_quota_us' => "200000\n",
                '/sys/fs/cgroup/cpu/other/cpu.cfs_period_us' => "100000\n",
            ],
            null,
        ];

        // See https://github.com/phpstan/phpstan-src/pull/6317
        yield 'v1: the cpu hierarchy is listed after other hierarchies' => [
            [
                '/proc/self/cgroup' => "7:net_cls,cpuset:/other\n4:cpu,cpuacct:/foo\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_quota_us' => "200000\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_period_us' => "100000\n",
            ],
            2,
        ];

        // The mount is rooted at the container's cgroup, so the path from
        // /proc/self/cgroup does not exist under /sys/fs/cgroup.
        // See https://github.com/opencontainers/runc/blob/v1.1.12/libcontainer/rootfs_linux.go#L526-L558
        yield 'v1: container in the host cgroup namespace' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/docker/0123abcd\n",
                '/sys/fs/cgroup/cpu,cpuacct/cpu.cfs_quota_us' => "400000\n",
                '/sys/fs/cgroup/cpu,cpuacct/cpu.cfs_period_us' => "100000\n",
            ],
            4,
        ];

        // The v2 hierarchy has no cpu controller, so no cpu.max.
        // See https://systemd.io/CGROUP_DELEGATION/#hierarchy-and-controller-support
        yield 'v1 and v2 (hybrid)' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/foo\n0::/foo\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_quota_us' => "300000\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_period_us' => "100000\n",
            ],
            3,
        ];
    }

    public function test_it_can_diagnose_the_quota_found(): void
    {
        $finder = new CgroupCpuQuotaFinder(
            new DummyFileReader([
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "250000 100000\n",
            ])
        );

        $expected = implode(
            PHP_EOL,
            [
                'Found the file "/proc/self/cgroup" with the content:',
                '0::/foo',
                'Found the CPU quotas:',
                '- /sys/fs/cgroup/foo/cpu.max: 2.5',
                'Will return "2".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }

    public function test_it_can_diagnose_the_absence_of_quota(): void
    {
        $finder = new CgroupCpuQuotaFinder(
            new DummyFileReader([
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "max 100000\n",
            ])
        );

        $expected = implode(
            PHP_EOL,
            [
                'Found the file "/proc/self/cgroup" with the content:',
                '0::/foo',
                'No CPU quota found.',
                'Will return "null".',
            ]
        );

        self::assertSame($expected, $finder->diagnose());
    }

    public function test_it_can_diagnose_the_absence_of_cgroup(): void
    {
        $finder = new CgroupCpuQuotaFinder(new DummyFileReader([]));

        self::assertSame(
            'Could not read the file "/proc/self/cgroup".',
            $finder->diagnose()
        );
    }
}
