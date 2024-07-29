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

/**
 * @readonly
 */
final class ParallelisationResult
{
    /**
     * @var positive-int
     */
    public $passedReservedCpus;

    /**
     * @var positive-int|null
     */
    public $passedLimit;

    /**
     * @var float<0, 1>|null
     */
    public $passedLoadLimitPerCore;

    /**
     * @var float<0, max>|null
     */
    public $passedSystemLoadAverage;

    /**
     * @var positive-int|null
     */
    public $correctedLimit;

    /**
     * @var float<0, max>
     */
    public $correctedSystemLoadAverage;

    /**
     * @var positive-int
     */
    public $totalCoresCount;

    /**
     * @var positive-int
     */
    public $availableCpus;

    /**
     * @param positive-int       $passedReservedCpus
     * @param positive-int|null  $passedLimit
     * @param float<0, 1>|null   $passedLoadLimitPerCore
     * @param float<0, max>|null $passedSystemLoadAverage
     * @param positive-int|null  $correctedLimit
     * @param float<0, max>      $correctedSystemLoadAverage
     * @param positive-int       $totalCoresCount
     * @param positive-int       $availableCpus
     */
    public function __construct(
        int $passedReservedCpus,
        ?int $passedLimit,
        ?float $passedLoadLimitPerCore,
        ?float $passedSystemLoadAverage,
        ?int $correctedLimit,
        float $correctedSystemLoadAverage,
        int $totalCoresCount,
        int $availableCpus
    ) {
        $this->passedReservedCpus = $passedReservedCpus;
        $this->passedLimit = $passedLimit;
        $this->passedLoadLimitPerCore = $passedLoadLimitPerCore;
        $this->passedSystemLoadAverage = $passedSystemLoadAverage;
        $this->correctedLimit = $correctedLimit;
        $this->correctedSystemLoadAverage = $correctedSystemLoadAverage;
        $this->totalCoresCount = $totalCoresCount;
        $this->availableCpus = $availableCpus;
    }
}
