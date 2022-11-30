# CPU Core Counter

This package is a tiny utility to get the number of CPU cores.

```sh
composer require fidry/cpu-core-counter
```


## Usage

```php
use Fidry\CpuCounter\CpuCoreCounter;

$counter = new CpuCoreCounter();
$counter->getCount();   // e.g. 8 
```


## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
