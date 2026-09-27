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

namespace Fidry\CpuCoreCounter\Test;

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;

/**
 * @internal
 * @readonly
 */
final class AvailableCpuCoresScenario
{
    /** @var list<CpuCoreFinder> */
    public $finders;
    /** @var CpuCoreFinder */
    public $countLimitFinder;
    /** @var positive-int|0 */
    public $reservedCpus;
    /** @var non-zero-int|null */
    public $countLimit;
    /** @var float|null */
    public $loadLimit;
    /** @var float|null */
    public $systemLoadAverage;
    /** @var positive-int */
    public $expected;

    /**
     * @param list<CpuCoreFinder> $finders
     * @param positive-int|0      $reservedCpus
     * @param non-zero-int|null   $countLimit
     * @param positive-int        $expected
     */
    public function __construct(
        array $finders,
        CpuCoreFinder $countLimitFinder,
        int $reservedCpus,
        ?int $countLimit,
        ?float $loadLimit,
        ?float $systemLoadAverage,
        int $expected
    ) {
        $this->finders = $finders;
        $this->countLimitFinder = $countLimitFinder;
        $this->reservedCpus = $reservedCpus;
        $this->countLimit = $countLimit;
        $this->loadLimit = $loadLimit;
        $this->systemLoadAverage = $systemLoadAverage;
        $this->expected = $expected;
    }

    /**
     * @param positive-int|null   $coresCountFound
     * @param positive-int|null   $countLimitFound
     * @param positive-int|0|null $reservedCpus
     * @param non-zero-int|null   $countLimit
     * @param positive-int        $expected
     *
     * @return array{self}
     */
    public static function create(
        ?int $coresCountFound,
        ?int $countLimitFound,
        ?int $reservedCpus,
        ?int $countLimit,
        ?float $loadLimit,
        ?float $systemLoadAverage,
        int $expected
    ): array {
        $finders = null === $coresCountFound
            ? []
            : [new DummyCpuCoreFinder($coresCountFound)];

        return [
            new self(
                $finders,
                null === $countLimitFound
                    ? new NullCpuCoreFinder()
                    : new DummyCpuCoreFinder($countLimitFound),
                $reservedCpus ?? 0,
                $countLimit,
                $loadLimit,
                $systemLoadAverage ?? 0.,
                $expected
            ),
        ];
    }
}
