#ace-storeinfo-getter

Get store info from ACE's store locator API. Supports some neat things.

## usage

packaging yet to come... install the class as needed, run `composer install` and use it like so:

```php
$storeNumber = 1234;
$store = new FSM\Ace\Store($storeNumber);
echo $store->getLocationName();
```

### caching

Caching of Ace store responses can be accomplished in two ways:

```php
define('FSM_ACE_CACHEFOLDER', __DIR__ . '/cache'); // Path to a writeable cache folder
$store = new FSM\Ace\Store(1234);

// or

$store = new FSM\Ace\Store(1234, __DIR__ . '/cache');
```

The Ace store response will be stored in the Ace cache folder as `<storenumber>.json`

### configuration

Configuration can be stored in YAML format at the location set using the constant `FSM_ACE_CONFIGPATH`

#### config.yaml

```yaml
map:
  exclusive: true               # Return only values with valid mappings?
  departments:                  # 'Original Value': 'New Value'
    'Automotive': 'Auto'
    'Clothing': 'Apparel'
    'Gas Stoves': 'Stoves'
  services:
    'Blade Sharpening': 'Knife Sharpening'
    'Chain Saw Sharpening': 'Chainsaw Sharpening'
    'Gift Card': 'Gift Cards'
    'Special Order 65,000+ items': 'Special Order Services'
  brands:
    'Webber Grills': 'Weber Grills'
```

#### PHP
```php
define('FSM_ACE_CONFIGPATH', __DIR__ . '/config.yaml'); // Path to a writeable cache folder
$store = new FSM\Ace\Store(1234);

$brands = $store->getBrands() // This will return values based on the mapping above
```
