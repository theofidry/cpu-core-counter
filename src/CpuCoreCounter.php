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
use InvalidArgumentException;
use function implode;
use function sprintf;
use function sys_getloadavg;
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
     * @param positive-int $reservedCpus
     * @param positive-int $limit
     * @param float        $loadLimitPerCore  Limits the number of CPUs based on the system load
     *                                        average per core in a range of [0., 1.].
     * @param float        $systemLoadAverage The system load average. If not provided, it will be
     *                                        retrieved using `sys_getloadavg()` to check the load
     *                                        of the system in the past minute. Should be a positive
     *                                        float.
     *
     * @see https://php.net/manual/en/function.sys-getloadavg.php
     */
    public function getAvailableForParallelisation(
        int $reservedCpus = 1,
        ?int $limit = null,
        ?float $loadLimitPerCore = .9,
        ?float $systemLoadAverage = null
    ): ParallelisationResult {
        self::checkLoadLimitPerCore($loadLimitPerCore);
        self::checkSystemLoadAverage($systemLoadAverage);

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

        return new ParallelisationResult(
            $reservedCpus,
            $limit,
            $loadLimitPerCore,
            $systemLoadAverage,
            $correctedLimit,
            $correctedSystemLoadAverage,
            $totalCoresCount,
            (int) $availableCpus
        );
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

    private static function checkLoadLimitPerCore(?float $loadLimitPerCore): void
    {
        if (null === $loadLimitPerCore) {
            return;
        }

        if ($loadLimitPerCore < 0. || $loadLimitPerCore > 1.) {
            throw new InvalidArgumentException(
                sprintf(
                    'The load limit per core must be in the range [0., 1.], got "%s".',
                    $loadLimitPerCore
                )
            );
        }
    }

    private static function checkSystemLoadAverage(?float $systemLoadAverage): void
    {
        if (null !== $systemLoadAverage && $systemLoadAverage < 0.) {
            throw new InvalidArgumentException(
                sprintf(
                    'The system load average must be a positive float, got "%s".',
                    $systemLoadAverage
                )
            );
        }
    }
}
