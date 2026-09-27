# CPU Core Counter

This package is a tiny utility to get the number of CPU cores.

```sh
composer require fidry/cpu-core-counter
```


## Usage

```php
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;

$counter = new CpuCoreCounter();

// For knowing the number of cores you can use for launching parallel processes:
$counter->getAvailableForParallelisation()->availableCpus;

// Get the number of CPU cores (by default it will use the logical cores count):
try {
    $counter->getCount();   // e.g. 8
} catch (NumberOfCpuCoreNotFound) {
    return 1;   // Fallback value
}

// Alternatively, to avoid having to catch the exception:

$counter = new CpuCoreCounter([
    ...CpuCoreCounter::getDefaultFinders(),
    new DummyCpuCoreFinder(1),  // Fallback value
]);

// A type-safe alternative form:
$counter->getCountWithFallback(1);

// Note that the result is memoized.
$counter->getCount();   // e.g. 8

```


## Advanced usage

### Changing the finders

When creating `CpuCoreCounter`, you can change the order of the finders or
disable specific ones by passing the list of finders to use:

```php
// Remove WindowsWmicFinder 
$finders = array_filter(
    CpuCoreCounter::getDefaultFinders(),
    static fn (CpuCoreFinder $finder) => !($finder instanceof WindowsWmicFinder)
);

$cores = (new CpuCoreCounter($finders))->getCount();
```

```php
// Use CPUInfo first & don't use Nproc
$finders = [
    new CpuInfoFinder(),
    new WindowsWmicFinder(),
    new HwLogicalFinder(),
];

$cores = (new CpuCoreCounter($finders))->getCount();
```

### Choosing only logical or physical finders

`FinderRegistry` provides two helpful entries:

- `::getDefaultLogicalFinders()`: gives an ordered list of finders that will
  look for the _logical_ CPU cores count.
- `::getDefaultPhysicalFinders()`: gives an ordered list of finders that will
  look for the _physical_ CPU cores count.

By default, `CpuCoreCounter` uses the logical finders, since this is usually
what you need and is also what the PHP source uses when building the PHP binary.


### Virtual machines

Inside a virtual machine (VM), for example a micro-VM such as a Docker Sandbox,
a CI runner or a cloud instance, the library only sees the CPUs of the VM, not
the CPUs of the host. This has a few consequences:

- The count is the number of virtual CPUs (vCPUs) given to the VM. If the host
  gives the VM more vCPUs than it has cores, the count is higher than what can
  really run in parallel. Nothing inside the VM shows this, so the fix is to
  give the VM at most as many vCPUs as the host has cores.
- A CPU limit that the host puts on the VM is not visible. The cgroup CPU quota
  check (see `getAvailableForParallelisation()`) only sees the cgroups of the
  VM's own kernel, e.g. a `docker run --cpus=2` inside the VM.
- The physical count comes from the CPU layout the hypervisor shows the VM,
  which it can choose freely. It does not tell you how many physical cores the
  host has, or whether two vCPUs share a core (SMT) or run on slower cores.
- The load average only covers the processes inside the VM. When the host is
  busy, the VM can report a low load and the `$loadLimit` of
  `getAvailableForParallelisation()` will not reduce the result.


### Containers

A container, e.g. Docker or LXC, shares the kernel of the host. The library
sees the host's CPUs, restricted to the ones the container may use, with two
caveats:

- A CPU quota set on a cgroup outside of the container's cgroup namespace is
  not found. For example, Proxmox VE applies the `cpulimit` of an LXC
  container to a parent cgroup the container cannot see. Pass `$countLimit`
  to `getAvailableForParallelisation()` instead.
- The load average may be the one of the host, unless the container
  virtualises it, e.g. with LXCFS. Pass `$systemLoadAverage` instead.


### Inspecting what the finders find on your system

Three scripts provide insight into what the finders find:

```shell
# Executes every finder and displays the result it found.
make execute                                     # From this repository
./vendor/fidry/cpu-core-counter/bin/execute.php  # From the library

# Executes every finder with details about how the result was obtained.
make diagnose                                     # From this repository
./vendor/fidry/cpu-core-counter/bin/diagnose.php  # From the library

# Displays the trace of CpuCoreCounter with all finders, then with the default ones.
php bin/trace.php                              # From this repository
./vendor/fidry/cpu-core-counter/bin/trace.php  # From the library
```


### Debugging the results

Three approaches help understand how a result was obtained:

1. If you use the default finder registries, the scripts described in the
   previous section provide detailed information.
2. To understand how the number of CPU cores was found, use
   `CpuCoreCounter::trace()`.
3. To understand how the number of CPU cores available for parallelisation was
   calculated, inspect the `ParallelisationResult` returned by
   `CpuCoreCounter::getAvailableForParallelisation()`.


## Backward Compatibility Promise (BCP)

The policy largely follows [Symfony's][symfony-bc-policy]. Code marked as
`@private` or `@internal` is excluded from the BCP.

The following elements are also excluded:

- The `diagnose`, `execute` and `trace` scripts: they are intended for debugging
  and inspection only.
- `FinderRegistry::get*Finders()`: finders may be added or reordered at any
  time.


## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) for how to set up the project, run the
tests, and understand the end-to-end tests and inspection builds.


## License

This package is licensed using the MIT License.

See [`LICENSE.md`](LICENSE.md) for details.

[symfony-bc-policy]: https://symfony.com/doc/current/contributing/code/bc.html
