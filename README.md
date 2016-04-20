#ace-storeinfo-getter

Get store info from ACE's store locator API. Supports some neat things.

## usage

packaging yet to come... install the class as needed, run `composer install` and use it like so:

```php
$storeNumber = 1234;
$store = new FSM\Ace\Store($storeNumber);
echo $store->getLocationName();
```
### requirements

* PHP >=5.6
* Composer requirements (symfony/yaml, guzzlehttp/guzzle)

### caching

Caching of Ace store responses can be accomplished in two ways:

```php
define('FSM_ACE_CACHEFOLDER', '/path/to/cache');
$store = new FSM\Ace\Store(1234);

// or

$store = new FSM\Ace\Store(1234, '/path/to/cache');
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
define('FSM_ACE_CONFIGPATH', '/path/to/config.yaml');
$store = new FSM\Ace\Store(1234);

$brands = $store->getServices(); // This will return values based on the mapping above
```
