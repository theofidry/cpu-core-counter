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

namespace Fidry\CpuCoreCounter;

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\EnvVariableFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;
use function sys_getloadavg;

final class CpuCoreCounter
{
    /**
     * @var list<CpuCoreFinder>
     */
    private $finders;

    /**
     * @var positive-int|null
     */
    private $count;

    /**
     * @param list<CpuCoreFinder>|null $finders
     */
    public function __construct(?array $finders = null)
    {
        $this->finders = $finders ?? FinderRegistry::getDefaultLogicalFinders();
    }

    /**
     * @param positive-int $reservedCpus
     *
     * @return positive-int
     *
     * @see https://php.net/manual/en/function.sys-getloadavg.php
     */
    public function getAvailableForParallelisation(
        int $reservedCpus = 1,
        ?int $limit = null,
        ?float $loadLimitPerCore = .9,
        ?float $systemLoadAverage = null
    ): int {
        $correctedLimit = null === $limit
            ? self::getKubernetesLimit()
            : $limit;

        $totalCoresCount = $this->getCountWithFallback(1);

        $availableCpus = max(1, $totalCoresCount - $reservedCpus);

        $correctedSystemLoadAverage = null === $systemLoadAverage
            ? sys_getloadavg()[0] ?? 0.
            : $systemLoadAverage;
        $systemLoadAveragePerCore = $correctedSystemLoadAverage / $availableCpus;

        // Adjust available CPUs based on current load
        if (null !== $loadLimitPerCore && $systemLoadAveragePerCore > $loadLimitPerCore) {
            $adjustedCpus = max(
                1,
                (1 - $systemLoadAveragePerCore) * $availableCpus
            );
            $availableCpus = min($availableCpus, $adjustedCpus);
        }

        if (null !== $correctedLimit && $availableCpus > $correctedLimit) {
            $availableCpus = $correctedLimit;
        }

        return (int) $availableCpus;
    }

    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    public function getCount(): int
    {
        // Memoize result
        if (null === $this->count) {
            $this->count = $this->findCount();
        }

        return $this->count;
    }

    /**
     * @param positive-int $fallback
     *
     * @return positive-int
     */
    public function getCountWithFallback(int $fallback): int
    {
        try {
            return $this->getCount();
        } catch (NumberOfCpuCoreNotFound $exception) {
            return $fallback;
        }
    }

    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    private function findCount(): int
    {
        foreach ($this->finders as $finder) {
            $cores = $finder->find();

            if (null !== $cores) {
                return $cores;
            }
        }

        throw NumberOfCpuCoreNotFound::create();
    }

    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return array{CpuCoreFinder, positive-int}
     */
    public function getFinderAndCores(): array
    {
        foreach ($this->finders as $finder) {
            $cores = $finder->find();

            if (null !== $cores) {
                return [$finder, $cores];
            }
        }

        throw NumberOfCpuCoreNotFound::create();
    }

    public static function getKubernetesLimit(): ?int
    {
        $finder = new EnvVariableFinder('KUBERNETES_CPU_LIMIT');

        return $finder->find();
    }
}
