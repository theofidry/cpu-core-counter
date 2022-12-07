# CPU Core Counter

This package is a tiny utility to get the number of CPU cores.

```sh
composer require fidry/cpu-core-counter
```


## Usage

```php
use Fidry\CpuCoreCounter\CpuCoreCounter;

$counter = new CpuCoreCounter();

try {
    $counter->getCount();   // e.g. 8
} catch (NumberOfCpuCoreNotFound) {
    return 1;   // Fallback value
}

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


## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
