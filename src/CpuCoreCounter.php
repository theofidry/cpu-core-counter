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

final class CpuCoreCounter
{
    /**
     * @var list<CpuCoreFinder>
     */
    private array $finders;

    /**
     * @var positive-int
     */
    private int $count;

    /**
     * @param list<CpuCoreFinder>|null $finders
     */
    public function __construct(?array $finders = null)
    {
        $this->finders = $finders ?? self::getDefaultFinders();
    }

    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    public function getCount(): int
    {
        // Memoize result
        if (!isset($this->count)) {
            $this->count = $this->findCount();
        }

        return $this->count;
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
     * @return list<CpuCoreFinder>
     */
    public static function getDefaultFinders(): array
    {
        return [
            new NProcFinder(),
            new WindowsWmicFinder(),
            new HwFinder(),
            new CpuInfoFinder(),
        ];
    }
}
