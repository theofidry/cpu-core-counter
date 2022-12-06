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

use Fidry\CpuCoreCounter\CpuCoreFinder;

final class DummyCpuCoreFinder implements CpuCoreFinder
{
    /**
     * @var int|null
     */
    private $count;

    public function __construct(?int $count)
    {
        $this->count = $count;
    }

    public function find(): ?int
    {
        return $this->count;
    }
}
