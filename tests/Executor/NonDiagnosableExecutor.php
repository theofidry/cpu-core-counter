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

namespace Fidry\CpuCoreCounter\Test\Executor;

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;

final class NonDiagnosableExecutor implements ProcessExecutor
{
    /**
     * @var array{string, string}|null
     */
    private $output;

    /**
     * @param array{string, string}|null $output
     */
    public function __construct(?array $output)
    {
        $this->output = $output;
    }

    public function execute(string $command): ?array
    {
        return $this->output;
    }
}
