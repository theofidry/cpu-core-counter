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

namespace Fidry\CpuCoreCounter\Test\AutoReview;

use Fidry\Makefile\Test\BaseMakefileTestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class MakefileTest extends BaseMakefileTestCase
{
    protected static function getMakefilePath(): string
    {
        return __DIR__.'/../../Makefile';
    }

    protected function getExpectedHelpOutput(): string
    {
        return <<<'EOF'
[33mUsage:[0m
  make TARGET

[32m#
# Commands
#---------------------------------------------------------------------------[0m
[33mdefault:[0m    Runs the default task
[33mdiagnose:[0m	 Executes a diagnosis for all the finders
[33mexecute:[0m	 Executes all the finders
[33mphive:[0m	 Updates a (registered) tool. E.g. make phive TOOL=infection
[33mcs:[0m 	    Fixes CS
[33mcs_lint:[0m    Lints CS
[33mautoreview:[0m	 Runs the AutoReview tests
[33mtest:[0m	 Runs all the tests
[33mphpunit:[0m    Runs PHPUnit
[33mphpunit_coverage_infection:[0m  Runs PHPUnit with code coverage for Infection
[33mphpunit_coverage_html:[0m  Runs PHPUnit with code coverage with HTML report
[33minfection:[0m  Runs infection
[33msecurity:[0m	 Runs the security check
[33mcomposer_audit:[0m  Runs a security analysis with Composer

EOF;
    }
}
