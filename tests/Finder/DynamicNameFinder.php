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

namespace Fidry\CpuCoreCounter\Test\Finder;

use DomainException;
use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use UnexpectedValueException;

final class DynamicNameFinder implements CpuCoreFinder
{
    /**
     * @var iterable<string>
     */
    private $names;

    /**
     * @param iterable<string> $names
     */
    public function __construct(iterable $names)
    {
        $this->names = $names;
    }

    public function diagnose(): string
    {
        throw new DomainException('Not implemented.');
    }

    public function find(): ?int
    {
        throw new DomainException('Not implemented.');
    }

    public function toString(): string
    {
        foreach ($this->names as $name) {
            return $name;
        }

        throw new UnexpectedValueException('No name found.');
    }
}
