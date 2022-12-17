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

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;

final class DummyProcOpenBasedFinder extends ProcOpenBasedFinder
{
    /**
     * @var callable
     */
    private $extraParse;

    /**
     * @param callable(?int):int|null $extraParse
     */
    public function __construct(
        ?callable $extraParse = null,
        ?ProcessExecutor $executor = null
    ) {
        parent::__construct($executor);

        $this->extraParse = $extraParse ?? static function (?int $value): ?int { return $value; };
    }

    protected function getCommand(): string
    {
        return '';
    }

    protected function countCpuCores(string $process): ?int
    {
        return ($this->extraParse)(parent::countCpuCores($process));
    }

    public function toString(): string
    {
        return 'DummyProcOpenBasedFinder';
    }
}
