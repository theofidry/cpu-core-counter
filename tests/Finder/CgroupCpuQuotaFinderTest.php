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
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_put_contents;
use function implode;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CgroupCpuQuotaFinder
 *
 * @internal
 */
final class CgroupCpuQuotaFinderTest extends TestCase
{
    /**
     * @var string|null
     */
    private $root;

    protected function tearDown(): void
    {
        if (null !== $this->root) {
            self::removeDirectory($this->root);
            $this->root = null;
        }
    }

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
        $finder = new CgroupCpuQuotaFinder($this->createFilesystem($files));

        self::assertSame($expected, $finder->find());
    }

    public static function quotaProvider(): iterable
    {
        yield 'no cgroup file' => [
            [],
            null,
        ];

        yield 'v2: quota on the process cgroup' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "200000 100000\n",
            ],
            2,
        ];

        yield 'v2: quota on a parent cgroup' => [
            [
                '/proc/self/cgroup' => "0::/foo/bar\n",
                '/sys/fs/cgroup/foo/cpu.max' => "300000 100000\n",
            ],
            3,
        ];

        yield 'v2: the lowest quota wins' => [
            [
                '/proc/self/cgroup' => "0::/foo/bar\n",
                '/sys/fs/cgroup/foo/cpu.max' => "100000 100000\n",
                '/sys/fs/cgroup/foo/bar/cpu.max' => "400000 100000\n",
            ],
            1,
        ];

        yield 'v2: unlimited' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "max 100000\n",
            ],
            null,
        ];

        yield 'v2: no cpu.max' => [
            [
                '/proc/self/cgroup' => "0::/\n",
            ],
            null,
        ];

        yield 'v2: fractional quota' => [
            [
                '/proc/self/cgroup' => "0::/foo\n",
                '/sys/fs/cgroup/foo/cpu.max' => "250000 100000\n",
            ],
            2,
        ];

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
        yield 'v2: container in its own cgroup namespace' => [
            [
                '/proc/self/cgroup' => "0::/\n",
                '/sys/fs/cgroup/cpu.max' => "200000 100000\n",
            ],
            2,
        ];

        yield 'v2: container in the host cgroup namespace' => [
            [
                '/proc/self/cgroup' => "0::/docker/0123abcd\n",
                '/sys/fs/cgroup/docker/0123abcd/cpu.max' => "400000 100000\n",
            ],
            4,
        ];

        yield 'v1: quota' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/foo\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_quota_us' => "200000\n",
                '/sys/fs/cgroup/cpu,cpuacct/foo/cpu.cfs_period_us' => "100000\n",
            ],
            2,
        ];

        yield 'v1: controller mounted as cpu' => [
            [
                '/proc/self/cgroup' => "4:cpu:/foo\n",
                '/sys/fs/cgroup/cpu/foo/cpu.cfs_quota_us' => "300000\n",
                '/sys/fs/cgroup/cpu/foo/cpu.cfs_period_us' => "100000\n",
            ],
            3,
        ];

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

        yield 'v1: cpuset and cpuacct are not the cpu controller' => [
            [
                '/proc/self/cgroup' => "7:cpuset:/other\n6:cpuacct:/other\n",
                '/sys/fs/cgroup/cpu/other/cpu.cfs_quota_us' => "200000\n",
                '/sys/fs/cgroup/cpu/other/cpu.cfs_period_us' => "100000\n",
            ],
            null,
        ];

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
        yield 'v1: container in the host cgroup namespace' => [
            [
                '/proc/self/cgroup' => "4:cpu,cpuacct:/docker/0123abcd\n",
                '/sys/fs/cgroup/cpu,cpuacct/cpu.cfs_quota_us' => "400000\n",
                '/sys/fs/cgroup/cpu,cpuacct/cpu.cfs_period_us' => "100000\n",
            ],
            4,
        ];

        // The v2 hierarchy has no cpu controller, so no cpu.max.
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
        $root = $this->createFilesystem([
            '/proc/self/cgroup' => "0::/foo\n",
            '/sys/fs/cgroup/foo/cpu.max' => "250000 100000\n",
        ]);
        $finder = new CgroupCpuQuotaFinder($root);

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
        $root = $this->createFilesystem([
            '/proc/self/cgroup' => "0::/foo\n",
            '/sys/fs/cgroup/foo/cpu.max' => "max 100000\n",
        ]);
        $finder = new CgroupCpuQuotaFinder($root);

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
        $finder = new CgroupCpuQuotaFinder($this->createFilesystem([]));

        self::assertSame(
            'Could not read the file "/proc/self/cgroup".',
            $finder->diagnose()
        );
    }

    /**
     * @param array<string, string> $files
     */
    private function createFilesystem(array $files): string
    {
        $root = sys_get_temp_dir().'/cpu-core-counter-cgroup-'.uniqid();
        $this->root = $root;

        mkdir($root, 0777, true);

        foreach ($files as $path => $contents) {
            $file = $root.$path;

            if (!is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }

            file_put_contents($file, $contents);
        }

        return $root;
    }

    private static function removeDirectory(string $directory): void
    {
        $entries = scandir($directory);

        foreach (false === $entries ? [] : $entries as $entry) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }

            $path = $directory.'/'.$entry;

            if (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
