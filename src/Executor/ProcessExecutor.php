<?php

declare(strict_types=1);

namespace Fidry\CpuCoreCounter\Executor;

interface ProcessExecutor
{
    /**
     * @return array{string, string}|null STDOUT & STDERR tuple
     */
    public function execute(string $command): ?array;
}
