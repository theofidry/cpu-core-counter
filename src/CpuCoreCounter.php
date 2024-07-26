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
use function implode;
use function sprintf;
use const PHP_EOL;

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
     * @param positive-int      $reservedCpus
     * @param positive-int|null $limit        If no limit is given, all available CPUs will be returned.
     *
     * @return positive-int
     */
    public function getAvailableForParallelisation(
        int $reservedCpus = 1,
        ?int $limit = null
    ): int {
        $limit = null === $limit
            ? self::getKubernetesLimit()
            : $limit;

        $count = $this->getCountWithFallback(1);

        $availableCpus = $count - $reservedCpus;

        if (null !== $limit && $availableCpus > $limit) {
            $availableCpus = $limit;
        }

        return max(1, $availableCpus);
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
     * This method is mostly for debugging purposes. 
     *
     * @return positive-int
     */
    public function trace(): string
    {
        $output = [];

        foreach ($this->finders as $finder) {
            $output[] = sprintf(
                'Executing the finder "%s":',
                $finder->toString()
            );
            $output[] = $finder->diagnose();

            $cores = $finder->find();

            if (null !== $cores) {
                $output[] = 'Result found: '.$cores;

                break;
            }

            $output[] = '–––';
        }

        return implode(PHP_EOL, $output);
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
