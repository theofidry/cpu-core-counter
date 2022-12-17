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

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\SkipOnOSFamilyFinder;
use PHPUnit\Framework\TestCase;
use function sprintf;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\SkipOnOSFamilyFinder
 *
 * @internal
 */
final class SkipOnOSFamilyFinderTest extends TestCase
{
    /**
     * @dataProvider describedFinderProvider
     */
    public function test_it_can_describe_itself(
        CpuCoreFinder $finder,
        string $expected
    ): void {
        self::assertSame(
            $expected,
            $finder->toString()
        );
    }

    public static function describedFinderProvider(): iterable
    {
        $shortName = FinderShortClassName::get(
            SkipOnOSFamilyFinder::forBSD(new NullCpuCoreFinder())
        );

        yield 'no family' => [
            new SkipOnOSFamilyFinder([], new NullCpuCoreFinder()),
            $shortName.'(skip=(),NullCpuCoreFinder)',
        ];

        yield 'windows' => [
            SkipOnOSFamilyFinder::forWindows(new NullCpuCoreFinder()),
            $shortName.'(skip=(Windows),NullCpuCoreFinder)',
        ];

        yield 'BSD' => [
            SkipOnOSFamilyFinder::forBSD(new NullCpuCoreFinder()),
            $shortName.'(skip=(BSD),NullCpuCoreFinder)',
        ];

        yield 'Darwin' => [
            SkipOnOSFamilyFinder::forDarwin(new NullCpuCoreFinder()),
            $shortName.'(skip=(Darwin),NullCpuCoreFinder)',
        ];

        yield 'Solaris' => [
            SkipOnOSFamilyFinder::forSolaris(new NullCpuCoreFinder()),
            $shortName.'(skip=(Solaris),NullCpuCoreFinder)',
        ];

        yield 'Linux' => [
            SkipOnOSFamilyFinder::forLinux(new NullCpuCoreFinder()),
            $shortName.'(skip=(Linux),NullCpuCoreFinder)',
        ];

        yield 'Arbitrary' => [
            new SkipOnOSFamilyFinder('MyFamily', new NullCpuCoreFinder()),
            $shortName.'(skip=(MyFamily),NullCpuCoreFinder)',
        ];

        yield 'multiple families' => [
            new SkipOnOSFamilyFinder(['Darwin', 'Solaris'], new NullCpuCoreFinder()),
            $shortName.'(skip=(Darwin,Solaris),NullCpuCoreFinder)',
        ];
    }

    public function test_it_can_diagnose_on_skipped_platform(): void
    {
        $finder = new SkipOnOSFamilyFinder(
            ['Linux', 'Darwin', 'Windows'],
            new NullCpuCoreFinder()
        );

        self::assertSame(
            sprintf(
                'Skipped platform detected ("%s").',
                PHP_OS_FAMILY
            ),
            $finder->diagnose()
        );
    }

    public function test_it_enriches_the_decorated_finder_diagnosis_on_non_skipped_platform(): void
    {
        $finder = new SkipOnOSFamilyFinder(
            [],
            new NullCpuCoreFinder()
        );

        self::assertSame(
            'Will return "null".',
            $finder->diagnose()
        );
    }

    public function test_it_skips_its_execution_when_on_the_specified_platform(): void
    {
        if (PHP_OS_FAMILY !== 'Linux' && PHP_OS_FAMILY !== 'Darwin') {
            self::markTestSkipped();
        }

        $finder = new SkipOnOSFamilyFinder(['Linux', 'Darwin'], new FakeFinder());

        self::assertNull($finder->find());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_it_does_not_skip_its_execution_when_not_on_the_specified_platform(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped();
        }

        $finder = SkipOnOSFamilyFinder::forWindows(new DummyCpuCoreFinder(1));

        self::assertSame(1, $finder->find());
    }

    public function it_handles_finders_with_dynamic_names(): void
    {
        $finder = new SkipOnOSFamilyFinder('', new DynamicNameFinder(['F1', 'F2']));

        self::assertSame('OnlyOnWindowsFinder(F1)', $finder->toString());
        self::assertSame('OnlyOnWindowsFinder(F2)', $finder->toString());
    }
}
