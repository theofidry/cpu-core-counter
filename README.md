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

try {
    $counter->getCount();   // e.g. 8
} catch (NumberOfCpuCoreNotFound) {
    return 1;   // Fallback value
}

// An alternative form where we not want to catch the exception:

$counter = new CpuCoreCounter([
    ...CpuCoreCounter::getDefaultFinders(),
    new DummyCpuCoreFinder(1),  // Fallback value
]);

$counter->getCount();   // e.g. 8

```


## Advanced usage

### Changing the finders

When creating `CpuCoreCounter`, you may want to change the order of the finders
used or disable a specific finder. You can easily do so by passing the finders
you want

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


### Checks what finders find what on your system

You have two commands available that provides insight about what the finders
can find:

```
$ make diagnosis                                    # From this repository
$ ./vendor/fidry/cpu-core-counter/bin/diagnose.php  # From the library
```

And:
```
$ make execute                                     # From this repository
$ ./vendor/fidry/cpu-core-counter/bin/execute.php  # From the library
```


## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
