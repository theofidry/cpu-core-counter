# Contributing

Thank you for your interest in contributing. This guide explains how to set up
the project, which checks are available, and how the continuous integration
(CI) is organised.

By participating in this project, you agree to follow the
[Code of Conduct](.github/CODE_OF_CONDUCT.md).


## Table of contents

- [Setting up the project](#setting-up-the-project)
- [Running the checks](#running-the-checks)
- [Test suites](#test-suites)
    - [Unit tests](#unit-tests)
    - [Legacy tests](#legacy-tests)
    - [Mutation testing](#mutation-testing)
    - [End-to-end tests](#end-to-end-tests)
- [Inspection builds](#inspection-builds)
- [Adding or changing a finder](#adding-or-changing-a-finder)
- [Continuous integration overview](#continuous-integration-overview)


## Setting up the project

The following tools are required:

- PHP 7.2 or later (the tooling itself requires a recent PHP version; the
  [integration workflow](.github/workflows/integration.yaml) shows which
  version each check uses).
- [Composer](https://getcomposer.org).
- GNU Make.
- [yamllint](https://yamllint.readthedocs.io/en/stable/quickstart.html), for
  linting the YAML files.
- Docker, only for running [zizmor](https://docs.zizmor.sh) locally.

If you use [direnv](https://direnv.net) with [Nix](https://nixos.org), the
[`.envrc`](.envrc) file provides GNU Make and yamllint for you.

The remaining tools (PHP-CS-Fixer, Infection and ComposerNormalize) are PHARs
managed by [PHIVE](https://phar.io). The Makefile installs them into `tools/`
on first use, so you do not need to install them manually.

To list all available commands:

```shell
make help
```


## Running the checks

Running `make` without a target executes the default task: the security audit,
the coding standard fixers, the auto-review checks and the test suite.

Before opening a pull request, run at least `make` to ensure the change passes
the same checks as the CI.


## Test suites

### Unit tests

The unit tests live in [`tests/`](tests) and are run with PHPUnit:

```shell
make phpunit
```

They cover the library logic in isolation: finders are tested against sample
command outputs or file contents (see, for example, `DummyExecutor`) rather
than the host system, so the results do not depend on the machine running them.

The CI runs them on every supported PHP version from 7.4 onwards.

### Legacy tests

PHP 7.2 and 7.3 are still supported, but several development dependencies
(PHPStan, the PHP-CS-Fixer configuration, `fidry/makefile` and
`webmozarts/strict-phpunit`) no longer install on these versions. The
_Legacy Tests_ CI job therefore removes them and runs PHPUnit with the
dedicated [`phpunit_legacy.xml.dist`](phpunit_legacy.xml.dist) configuration.

To reproduce it locally with PHP 7.2 or 7.3:

```shell
composer remove --dev --no-update \
    fidry/makefile \
    fidry/php-cs-fixer-config \
    webmozarts/strict-phpunit \
    phpstan/*
composer update
vendor/bin/phpunit --configuration phpunit_legacy.xml.dist
```

Remember to revert the changes to `composer.json` afterwards.

### Mutation testing

The project uses [Infection](https://infection.github.io) with a target Mutation
Score Indicator (MSI) of 100%:

```shell
make test
```

A surviving mutant usually means a test case is missing or an assertion is too
loose. If a mutant cannot reasonably be killed, ignore it in
[`infection.json5`](infection.json5) with a comment explaining why, and mention
it in the pull request.

### End-to-end tests

The unit tests cannot verify that a finder actually works on a real system. The
end-to-end tests fill that gap: they execute every finder registered in
`FinderRegistry::getAllVariants()` on the host and compare the outcome with a
committed expectation.

[`e2e/execute-finders.php`](e2e/execute-finders.php) prints one line per
finder, in the form `<finder>: <result>`, where the result is:

- `.` if the finder found a CPU core count;
- `F` if the finder did not find one.

The output is written to `e2e/actual-output` and compared, ignoring whitespace,
with `e2e/expected-output`. The test fails if the two differ.

The expected output depends on the operating system and on the PHP
restrictions in place. There is one expectation file per scenario, and the CI
copies the relevant one to `e2e/expected-output` before running the test:

| Scenario            | CI runner        | Script                             | Expected output                         |
|---------------------|------------------|------------------------------------|-----------------------------------------|
| Ubuntu              | `ubuntu-latest`  | `make e2e`                         | `e2e/expected-output-ubuntu`            |
| macOS               | `macos-latest`   | `make e2e`                         | `e2e/expected-output-osx`               |
| Windows             | `windows-latest` | `./e2e/test-finders.sh`            | `e2e/expected-output-windows`           |
| Ubuntu (restricted) | `ubuntu-latest`  | `./e2e/test-restricted-finders.sh` | `e2e/expected-output-ubuntu-restricted` |

Notes on the scenarios:

- **Windows** calls the shell script directly because GNU Make is not
  available on the runner.
- **Ubuntu (restricted)** simulates a locked-down PHP environment, as found on
  some shared hosts. PHP runs with:
    - `open_basedir` limited to the parent directory of the project and the
      system temporary directory, which prevents reading files such as
      `/proc/cpuinfo`;
    - `disable_functions` set to `exec`, `passthru`, `proc_open`, `shell_exec`,
      `system`, `popen`, `pcntl_exec` and `pcntl_fork`, which prevents running
      any external command.

  In this environment, every finder relying on the file system or on a
  process is expected to fail gracefully, i.e. report `F` rather than crash.

To run an end-to-end test locally, copy the expectation matching your system
first. For example, on Linux:

```shell
cp e2e/expected-output-ubuntu e2e/expected-output
make e2e

# Or, for the restricted scenario:
cp e2e/expected-output-ubuntu-restricted e2e/expected-output
./e2e/test-restricted-finders.sh
```

`e2e/expected-output` and `e2e/actual-output` are ignored by Git. The committed
expectations reflect the GitHub-hosted runners, so the result on your machine
may legitimately differ, for example if a command such as `lscpu` or `nproc` is
not installed.


## Inspection builds

The end-to-end tests only check _whether_ a finder returns a value. The
[Inspection workflow](.github/workflows/inspection.yaml) shows _what_ each
finder returns and why, on a range of real environments.

For each runner below, it executes both debugging scripts:

- [`bin/execute.php`](bin/execute.php) (`make execute`): runs every finder and
  prints the value it found.
- [`bin/diagnose.php`](bin/diagnose.php) (`make diagnose`): runs every finder
  with the details of how the result was obtained (e.g. the command executed
  and its raw output). It then does the same for the default logical and
  physical finders.

| Job                | Runner           |
|--------------------|------------------|
| Ubuntu Inspection  | `ubuntu-latest`  |
| Windows Inspection | `windows-latest` |
| OSX Inspection     | `macos-latest`   |
| OSX 15 Inspection  | `macos-15`       |

The inspection builds make no assertion about the values found: they fail only
if a script errors. Their purpose is to provide a reference output to consult
in the job logs when:

- adding or changing a finder, to confirm it behaves as intended on each
  operating system;
- updating an end-to-end expectation file, to understand why a finder now
  succeeds or fails;
- investigating a bug report, to compare the reporter's output with a known
  environment.

The workflow runs on every pull request, on every push to `main`, and monthly,
which helps detect changes introduced by new runner images.

A third script, [`bin/trace.php`](bin/trace.php), prints the trace of
`CpuCoreCounter` for all finders, then for the default logical and physical
finders. It is not part of the inspection builds, but it is useful when
debugging locally.


## Adding or changing a finder

When adding a finder or changing the behaviour of an existing one:

1. Add or update its unit tests in [`tests/Finder/`](tests/Finder).
2. Register it in [`FinderRegistry`](src/Finder/FinderRegistry.php):
    - in `getAllVariants()`, so that the end-to-end tests and the debugging
      scripts cover it;
    - in `getDefaultLogicalFinders()` and/or `getDefaultPhysicalFinders()` if
      it should be used by default.
3. Update every `e2e/expected-output-*` file with the result expected on the
   corresponding environment.
4. Check the [inspection builds](#inspection-builds) of your pull request to
   confirm that the finder returns the expected values on each operating
   system.
