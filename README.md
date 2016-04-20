#ace-store

Get store info from ACE's store locator API. Supports some neat things.

## usage

Install using composer:
```bash
composer require footsteps-marketing/ace-store
```

Then use it in your project

```php
require __DIR__ . '/vendor/autoload.php';

$storeNumber = 1234;
$store = new FSM\Ace\Store($storeNumber);
echo $store->getLocationName();
```

### requirements

* PHP >=5.6

### caching

Caching of Ace store responses can be accomplished in two ways:

```php
define('FSM_ACE_CACHE_FOLDER', '/path/to/cache');
$store = new FSM\Ace\Store(1234);

// or

$store = new FSM\Ace\Store(1234, '/path/to/cache');
```

Cache lifetime defaults to one week -- it can be modified by setting the constant `FSM_ACE_CACHE_LIFETIME`

```php
define('FSM_ACE_CACHE_LIFETIME', 1 * 24 * 60 * 60); // Desired lifetime in seconds
define('FSM_ACE_CACHE_FOLDER', '/path/to/cache');
$store = new FSM\Ace\Store(1234);
```

The Ace store response will be stored in the Ace cache folder as `<storenumber>.json`

### configuration

Configuration can be stored in YAML format at the location set using the constant `FSM_ACE_CONFIG_PATH`

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
define('FSM_ACE_CONFIG_PATH', '/path/to/config.yaml');
$store = new FSM\Ace\Store(1234);

$brands = $store->getServices(); // This will return values based on the mapping above
```
