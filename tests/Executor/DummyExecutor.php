<?php

declare(strict_types=1);

namespace Fidry\CpuCoreCounter\Test\Executor;

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;

final class DummyExecutor implements ProcessExecutor
{
    /**
     * @var array{string, string}|null
     */
    private $output;

    /**
     * @param array{string, string}|null $output
     */
    public function setOutput(?array $output): void
    {
        $this->output = $output;
    }

    public function execute(string $command): ?array
    {
        return $this->output ?? null;
    }
}
