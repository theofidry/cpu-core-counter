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

/**
 * @internal
 * @readonly
 */
final class AvailableCpuCoresScenario
{
    /** @var list<CpuCoreFinder> */
    public $finders;
    /** @var array<string, string|null> */
    public $environmentVariables;
    /** @var positive-int|0 */
    public $reservedCpus;
    /** @var positive-int */
    public $countLimit;
    /** @var float|null */
    public $loadLimitPerCore;
    /** @var float|null */
    public $systemLoadAverage;
    /** @var positive-int */
    public $expected;

    /**
     * @param list<CpuCoreFinder>            $finders
     * @param array<string, string|int|null> $environmentVariables
     * @param positive-int|0                 $reservedCpus
     * @param positive-int                   $limit
     * @param positive-int                   $expected
     */
    public function __construct(
        array $finders,
        array $environmentVariables,
        int $reservedCpus,
        ?int $limit,
        ?float $loadLimit,
        ?float $systemLoadAverage,
        int $expected
    ) {
        $this->finders = $finders;
        $this->environmentVariables = $environmentVariables;
        $this->reservedCpus = $reservedCpus;
        $this->countLimit = $limit;
        $this->loadLimitPerCore = $loadLimit;
        $this->systemLoadAverage = $systemLoadAverage;
        $this->expected = $expected;
    }

    /**
     * @param positive-int|null              $coresCountFound
     * @param array<string, string|int|null> $environmentVariables
     * @param positive-int|0|null            $reservedCpus
     * @param positive-int|null              $countLimit
     * @param positive-int                   $expected
     *
     * @return array{self}
     */
    public static function create(
        ?int $coresCountFound,
        array $environmentVariables,
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
                $environmentVariables,
                $reservedCpus ?? 0,
                $countLimit,
                $loadLimit,
                $systemLoadAverage ?? 0.,
                $expected
            ),
        ];
    }
}
