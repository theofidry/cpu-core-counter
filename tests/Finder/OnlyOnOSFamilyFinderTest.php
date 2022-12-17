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
use Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder;
use PHPUnit\Framework\TestCase;
use function sprintf;
use const PHP_OS_FAMILY;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\OnlyOnOSFamilyFinder
 *
 * @internal
 */
final class OnlyOnOSFamilyFinderTest extends TestCase
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
            OnlyOnOSFamilyFinder::forBSD(new NullCpuCoreFinder())
        );

        yield 'no family' => [
            new OnlyOnOSFamilyFinder([], new NullCpuCoreFinder()),
            $shortName.'(only=(),NullCpuCoreFinder)',
        ];

        yield 'windows' => [
            OnlyOnOSFamilyFinder::forWindows(new NullCpuCoreFinder()),
            $shortName.'(only=(Windows),NullCpuCoreFinder)',
        ];

        yield 'BSD' => [
            OnlyOnOSFamilyFinder::forBSD(new NullCpuCoreFinder()),
            $shortName.'(only=(BSD),NullCpuCoreFinder)',
        ];

        yield 'Darwin' => [
            OnlyOnOSFamilyFinder::forDarwin(new NullCpuCoreFinder()),
            $shortName.'(only=(Darwin),NullCpuCoreFinder)',
        ];

        yield 'Solaris' => [
            OnlyOnOSFamilyFinder::forSolaris(new NullCpuCoreFinder()),
            $shortName.'(only=(Solaris),NullCpuCoreFinder)',
        ];

        yield 'Linux' => [
            OnlyOnOSFamilyFinder::forLinux(new NullCpuCoreFinder()),
            $shortName.'(only=(Linux),NullCpuCoreFinder)',
        ];

        yield 'Arbitrary' => [
            new OnlyOnOSFamilyFinder('MyFamily', new NullCpuCoreFinder()),
            $shortName.'(only=(MyFamily),NullCpuCoreFinder)',
        ];

        yield 'multiple families' => [
            new OnlyOnOSFamilyFinder(['Darwin', 'Solaris'], new NullCpuCoreFinder()),
            $shortName.'(only=(Darwin,Solaris),NullCpuCoreFinder)',
        ];
    }

    public function test_it_can_diagnose_on_skipped_platform(): void
    {
        $finder = new OnlyOnOSFamilyFinder(
            [],
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
        $finder = new OnlyOnOSFamilyFinder(
            ['Windows', 'Linux', 'Darwin'],
            new NullCpuCoreFinder()
        );

        self::assertSame(
            'Will return "null".',
            $finder->diagnose()
        );
    }

    public function test_it_skips_its_execution_when_not_on_the_specified_platform(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped();
        }

        $finder = OnlyOnOSFamilyFinder::forWindows(new FakeFinder());

        self::assertNull($finder->find());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_it_does_not_skip_its_execution_when_not_on_the_specified_platform(): void
    {
        if (PHP_OS_FAMILY !== 'Linux' && PHP_OS_FAMILY !== 'Darwin') {
            self::markTestSkipped();
        }

        $finder = new OnlyOnOSFamilyFinder(['Linux', 'Darwin'], new DummyCpuCoreFinder(1));

        self::assertSame(1, $finder->find());
    }

    public function it_handles_finders_with_dynamic_names(): void
    {
        $finder = new OnlyOnOSFamilyFinder('', new DynamicNameFinder(['F1', 'F2']));

        self::assertSame('OnlyOnWindowsFinder(F1)', $finder->toString());
        self::assertSame('OnlyOnWindowsFinder(F2)', $finder->toString());
    }
}
