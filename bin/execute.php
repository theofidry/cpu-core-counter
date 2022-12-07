#!/usr/bin/env php
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

use Fidry\CpuCoreCounter\Diagnoser;

require_once __DIR__.'/../vendor/autoload.php';

echo Diagnoser::execute().PHP_EOL;
