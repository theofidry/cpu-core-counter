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

use function array_map;
use function array_values;
use function count;
use function implode;
use function min;
use function sprintf;
use const PHP_EOL;

/**
 * Executes all the decorated finders and returns the lowest result found.
 * Unlike the list of finders given to CpuCoreCounter, which stops at the
 * first result, this suits limits, of which the strictest one applies.
 */
final class LowestCpuCoreFinder implements CpuCoreFinder
{
    /**
     * @var list<CpuCoreFinder>
     */
    private $decoratedFinders;

    public function __construct(CpuCoreFinder ...$decoratedFinders)
    {
        $this->decoratedFinders = array_values($decoratedFinders);
    }

    public function diagnose(): string
    {
        $diagnoses = array_map(
            static function (CpuCoreFinder $finder): string {
                return $finder->toString().':'.PHP_EOL.$finder->diagnose();
            },
            $this->decoratedFinders
        );

        $diagnoses[] = sprintf(
            'Will return "%s".',
            $this->find() ?? 'null'
        );

        return implode(PHP_EOL, $diagnoses);
    }

    public function find(): ?int
    {
        $cores = [];

        foreach ($this->decoratedFinders as $finder) {
            $result = $finder->find();

            if (null !== $result) {
                $cores[] = $result;
            }
        }

        return 0 === count($cores) ? null : min($cores);
    }

    public function toString(): string
    {
        return sprintf(
            'LowestCpuCoreFinder(%s)',
            implode(
                ',',
                array_map(
                    static function (CpuCoreFinder $finder): string {
                        return $finder->toString();
                    },
                    $this->decoratedFinders
                )
            )
        );
    }
}
