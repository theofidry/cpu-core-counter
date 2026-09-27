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

namespace Fidry\CpuCoreCounter\Finder;

final class FinderRegistry
{
    /**
     * @return list<CpuCoreFinder> List of all the known finders with all their variants.
     */
    public static function getAllVariants(): array
    {
        return [
            new CgroupCpuQuotaFinder(),
            new CpuInfoFinder(),
            new DummyCpuCoreFinder(1),
            new HwLogicalFinder(),
            new HwPhysicalFinder(),
            new LscpuLogicalFinder(),
            new LscpuPhysicalFinder(),
            new _NProcessorFinder(),
            new NProcessorFinder(),
            new NProcFinder(true),
            new NProcFinder(false),
            new NullCpuCoreFinder(),
            SkipOnOSFamilyFinder::forWindows(
                new DummyCpuCoreFinder(1)
            ),
            OnlyOnOSFamilyFinder::forWindows(
                new DummyCpuCoreFinder(1)
            ),
            new CmiCmdletLogicalFinder(),
            new CmiCmdletPhysicalFinder(),
            new WindowsRegistryLogicalFinder(),
            new WmicPhysicalFinder(),
            new WmicLogicalFinder(),
        ];
    }

    /**
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultLogicalFinders(): array
    {
        return [
            OnlyOnOSFamilyFinder::forWindows(new WindowsRegistryLogicalFinder()),
            OnlyOnOSFamilyFinder::forWindows(new CmiCmdletLogicalFinder()),
            OnlyOnOSFamilyFinder::forWindows(new WmicLogicalFinder()),
            new NProcFinder(),
            new HwLogicalFinder(),
            new _NProcessorFinder(),
            new NProcessorFinder(),
            new LscpuLogicalFinder(),
            new CpuInfoFinder(),
        ];
    }

    /**
     * Inside a virtual machine, these finders count the cores of the CPU layout
     * the hypervisor shows the VM, not the physical cores of the host. There is
     * no reliable way to detect a VM on every platform, so the library does not
     * try: it is up to you to decide whether to trust this count in a VM.
     *
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultPhysicalFinders(): array
    {
        return [
            OnlyOnOSFamilyFinder::forWindows(new CmiCmdletPhysicalFinder()),
            OnlyOnOSFamilyFinder::forWindows(new WmicPhysicalFinder()),
            new HwPhysicalFinder(),
            new LscpuPhysicalFinder(),
        ];
    }

    /**
     * @return CpuCoreFinder Finds the maximum number of cores to use, rather than the
     *                       number of cores. CpuCoreCounter uses it when no count
     *                       limit is given to getAvailableForParallelisation().
     */
    public static function getDefaultCountLimitFinder(): CpuCoreFinder
    {
        return new FirstCpuCoreFinder(
            new CgroupCpuQuotaFinder(),
            new EnvVariableFinder('KUBERNETES_CPU_LIMIT')
        );
    }

    private function __construct()
    {
    }
}
