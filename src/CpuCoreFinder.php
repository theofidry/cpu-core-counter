<?php

declare(strict_types=1);

namespace Fidry\CpuCounter;

interface CpuCoreFinder
{
    /**
     * @return positive-int|null
     */
    public static function find(): ?int;
}
