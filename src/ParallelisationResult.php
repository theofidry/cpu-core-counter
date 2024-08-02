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
     * @var positive-int|0
     */
    public $passedReservedCpus;

    /**
     * @var positive-int|null
     */
    public $passedLimit;

    /**
     * @var float|null
     */
    public $passedLoadLimitPerCore;

    /**
     * @var float|null
     */
    public $passedSystemLoadAverage;

    /**
     * @var positive-int|null
     */
    public $correctedLimit;

    /**
     * @var float
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
     * @param positive-int|0    $passedReservedCpus
     * @param positive-int|null $passedLimit
     * @param positive-int      $totalCoresCount
     * @param positive-int      $availableCpus
     */
    public function __construct(
        int $passedReservedCpus,
        ?int $passedLimit,
        ?float $passedLoadLimitPerCore,
        ?float $passedSystemLoadAverage,
        ?int $correctedLimit,
        ?float $correctedSystemLoadAverage,
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
