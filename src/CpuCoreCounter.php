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
     * @param positive-int|0 $reservedCpus      Number of CPUs to reserve. This is useful when you want
     *                                          to reserve some CPUs for other processes. If the main
     *                                          process is going to be busy still, you may want to set
     *                                          this value to 1.
     * @param float|null $systemLoadLimit       Element of [0., 1.]. Limits the number of CPUs based
     *                                          on the system load average. Set it to null or 1. to
     *                                          disable the check, it otherwise will adjust the number
     *                                          of CPUs based on the system load average. For example
     *                                          if 3 cores out of 10 are busy and the load limit is
     *                                          set to 50%, only 2 cores will be available for
     *                                          parallelisation. The reserved cores are also taken
     *                                          into consideration, i.e. if .7 is passed, it will
     *                                          consider the max system load limit should not be
     *                                          higher than 70% for the system cores minus the reserved
     *                                          ones.
     * @param float      $systemLoadAverage     The system load average. If not provided, it will be
     *                                          retrieved using `sys_getloadavg()` to check the load
     *                                          of the system in the past minute. Should be a positive
     *                                          float.
     *
     * @see https://php.net/manual/en/function.sys-getloadavg.php
     */
    public function getAvailableForParallelisation(
        int $reservedCpus = 0,
        ?int $limit = null,
        ?float $systemLoadLimit = .9,
        ?float $systemLoadAverage = null
    ): ParallelisationResult {
        self::checkLoadLimitPerCore($systemLoadLimit);
        self::checkSystemLoadAverage($systemLoadAverage);

        $correctedLimit = null === $limit
            ? self::getKubernetesLimit()
            : $limit;

        $totalCoreCount = $this->getCountWithFallback(1);
        $availableCores = max(1, $totalCoreCount - $reservedCpus);

        // Adjust available CPUs based on current load
        if (null !== $systemLoadLimit) {
            $correctedSystemLoadAverage = null === $systemLoadAverage
                ? sys_getloadavg()[0] ?? 0.
                : $systemLoadAverage;

            $numberOfFreeCores = max(
                1,
                $systemLoadLimit * $totalCoreCount - $correctedSystemLoadAverage
            );

            $availableCores = min($availableCores, $numberOfFreeCores);
        }

        if (null !== $correctedLimit && $availableCores > $correctedLimit) {
            $availableCores = $correctedLimit;
        }

        return new ParallelisationResult(
            $reservedCpus,
            $limit,
            $systemLoadLimit,
            $systemLoadAverage,
            $correctedLimit,
            $correctedSystemLoadAverage ?? $systemLoadAverage,
            $totalCoreCount,
            (int) $availableCores
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
