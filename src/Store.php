<?php namespace FootstepsMarketing\Ace;

use GuzzleHttp\Client;
use Exception;

/**
 * An object containing an Ace Hardware store's info,
 * based on aresponse from the Ace Hardware store locator.
 */
class Store
{
    const ACE_REQUEST_URL_FORMAT = "http://www.acehardware.com/storeLocServ?heavy=true&token=ACE&operation=storeData&storeID=%05d";
    const ACE_LANDING_PAGE_URL_FORMAT = "http://www.acehardware.com/mystore/index.jsp?store=%05d";

    private $locationName;
    private $address;
    private $postalCode;
    private $stateCode;
    private $city;
    private $phoneNumber;
    private $storeInfoURL;
    private $storeBiography;
    private $owner;
    private $staff;
    private $storeNumber;
    private $mapCoords;
    private $hours;
    private $departments;
    private $services;
    private $brands;
    private $chain;

    private $cacheFolder;
    private $cacheLifetime;
    private $url;
    private $body;
    private $storeObject;
    private $longitude;
    private $latitude;


    /**
     * Initialize a Store object
     *
     * Will use a cache folder if it's writeable, specified either in the constructor or
     * in the constant `FSM_ACE_CACHE_FOLDER` -- cache lifetime to be specified in the
     * constant `FSM_ACE_CACHE_LIFETIME` (as an integer, in seconds)
     *
     * @param int $storeNumber The store number
     * @param string $cacheFolder Path to the cache folder
     */
    public function __construct(int $storeNumber, $cacheFolder = null)
    {
        $this->cacheFolder = $cacheFolder;

        if (is_null($this->cacheFolder) && defined('FSM_ACE_CACHE_FOLDER')) {
            $this->cacheFolder = FSM_ACE_CACHE_FOLDER;
        }
        $this->cacheLifetime = (defined('FSM_ACE_CACHE_LIFETIME')) ? FSM_ACE_CACHE_LIFETIME : 7 * 24 * 24 * 60;

        $this->storeNumber = $storeNumber;
        $this->url = sprintf(self::ACE_REQUEST_URL_FORMAT, $storeNumber);
        $this->initialize();
    }


    /**
     * Magic getter
     *
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property) && $this->$property) {
            return $this->$property;
        }
        $propertyGetter = sprintf('get%s', ucfirst($property));
        $this->$property = $this->$propertyGetter();
        return $this->$property;
    }


    /**
     * Load the store object (remotely or from cache)
     */
    private function initialize()
    {
        $cachePath = $this->cacheFolder . '/' . $this->storeNumber . '.json';
        if (is_null($this->cacheFolder) ||
            time() - filemtime($cachePath) > $this->cacheLifetime ||
            !is_readable($cachePath)
        ) {
            $client = new Client();
            $response = $client->get($this->url);

            if ($response->getStatusCode() !== 200) {
                throw new Exception(sprintf("Error %d", $response->getStatusCode()));
            }

            $this->body = $response->getBody()->getContents();
            if (is_writeable(dirname($cachePath))) {
                file_put_contents($cachePath, $this->body);
            }
        } else {
            $this->body = file_get_contents($cachePath);
        }
        $this->storeObject = json_decode($this->body);
    }


    /**
     * Get the location name
     * @return string
     */
    public function getLocationName()
    {
        return $this->storeObject->locationName;
    }


    /**
     * Get the location's address
     * @return string
     */
    public function getAddress()
    {
        $address = [];
        $i = 1;
        while (property_exists($this->storeObject, "address{$i}")) {
            $address[] = $this->storeObject->{"address{$i}"};
            $i++;
        }
        return implode(',', $address);
    }


    /**
     * Get the location's phone number
     * @return string
     */
    public function getPostalCode()
    {
        return $this->storeObject->postalCode;
    }


    /**
     * Get the location's state
     * @return string
     */
    public function getStateCode()
    {
        return $this->storeObject->stateCode;
    }


    /**
     * Get the location's state
     * @return string
     */
    public function getCity()
    {
        return $this->storeObject->city;
    }


    /**
     * Get the location's phone number
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumberfy($this->storeObject->phoneNumber);
    }


    /**
     * Get the location's URL
     * @return string
     */
    public function getStoreInfoURL()
    {
        return (!empty($this->storeObject->storeInfoURL)) ? $this->storeObject->storeInfoURL : sprintf(self::ACE_LANDING_PAGE_URL_FORMAT, $this->storeNumber);
    }


    /**
     * Get the location's biography
     * @return string
     */
    public function getStoreBiography()
    {
        return $this->storeObject->storeBiography;
    }


    /**
     * Get the location's owner
     * @return string
     */
    public function getOwner()
    {
        $owner = $this->storeObject->owner;
        $owner = array_reduce($owner, function ($carry, $item) {
            $carry .= (!empty($carry)) ? ", {$item->fullName}" : $item->fullName;
            return $carry;
        });
        return $owner;
    }


    /**
     * Get the location's staff
     * @return array
     */
    public function getStaff()
    {
        $staff = $this->storeObject->storeStaff;
        $staff = array_map(function ($person) {
            return [
                'fullName' => $person->fullName,
                'personTitle' => $person->personTitle,
                'personImageUrl' => $person->personImageUrl,
            ];
        }, $staff);
        return $staff;
    }


    /**
     * Get the location's store number
     * @return array
     */
    public function getStoreNumber()
    {
        return $this->storeNumber;
    }


    /**
     * Get the location's map coordinates
     * @return array Latitude and Longitude
     */
    public function getMapCoords()
    {
        return [
            $this->storeObject->latitude,
            $this->storeObject->longitude,
        ];
    }


    /**
     * Get the location's hours
     * @return array Hours by day
     */
    public function getHours()
    {
        $days = [
            'Mon' => 'Monday',
            'Tue' => 'Tuesday',
            'Wed' => 'Wednesday',
            'Thu' => 'Thursday',
            'Fri' => 'Friday',
            'Sat' => 'Saturday',
            'Sun' => 'Sunday',
        ];

        $hours = [];

        foreach (get_object_vars($this->storeObject->hours) as $prop => $value) {
            $hours[$prop] = str_replace('.', ':', ($value > 1200) ? sprintf("%.2fpm", ($value % 1200) / 100) : sprintf("%.2fam", ($value) / 100));
        }

        $hours = [
            'Monday' => $hours["openingTimeMon"] . ' - ' . $hours['closingTimeMon'],
            'Tuesday' => $hours["openingTimeTue"] . ' - ' . $hours['closingTimeTue'],
            'Wednesday' => $hours["openingTimeWed"] . ' - ' . $hours['closingTimeWed'],
            'Thursday' => $hours["openingTimeThu"] . ' - ' . $hours['closingTimeThu'],
            'Friday' => $hours["openingTimeFri"] . ' - ' . $hours['closingTimeFri'],
            'Saturday' => $hours["openingTimeSat"] . ' - ' . $hours['closingTimeSat'],
            'Sunday' => $hours["openingTimeSun"] . ' - ' . $hours['closingTimeSun'],
        ];
        return $hours;
    }


    /**
     * Return a different value for a key based on a mapping array
     * @param  string|int $value Original value
     * @param  array $map Map of values (`'original' => 'new'`)
     * @return string|int   The mapped value (or null if the value doesn't exist in the map)
     */
    private static function map($value, $map)
    {
        if (is_null($map)) {
            return $value;
        }

        if (array_key_exists($value, $map)) {
            return $map[$value];
        }

        if (Config::get('map', 'exclusive') === true) {
            return null;
        }

        return $value;
    }

    /**
     * Get the location's departments
     * @return array Departments
     */
    public function getDepartments()
    {
        $departments = array_merge(
            is_array($this->storeObject->departments) ? $this->storeObject->departments : [],
            is_array($this->storeObject->customDepartments) ? $this->storeObject->customDepartments : []
        );

        $map = Config::get('map', 'departments');

        $departments = array_values(array_unique(array_filter(array_map(function ($item) use ($map) {
            return self::map($item->featureLongDesc, $map);
        }, $departments))));
        return !empty($departments) ? $departments : [];
    }


    /**
     * Get the location's services
     * @return array Services
     */
    public function getServices()
    {
        $services = array_merge(
            is_array($this->storeObject->standardServices) ? $this->storeObject->standardServices : [],
            is_array($this->storeObject->customServices) ? $this->storeObject->customServices : []
        );

        $map = Config::get('map', 'services');

        $services = array_values(array_unique(array_filter(array_map(function ($item) use ($map) {
            return self::map($item->featureLongDesc, $map);
        }, $services))));
        return !empty($services) ? $services : [];
    }


    /**
     * Get the location's brands
     * @return array Brands
     */
    public function getBrands()
    {
        $brands = array_merge(
            is_array($this->storeObject->specialtyBrands) ? $this->storeObject->specialtyBrands : [],
            is_array($this->storeObject->customSpecialtyBrand) ? $this->storeObject->customSpecialtyBrand : []
        );

        $map = Config::get('map', 'brands');

        $brands = array_values(array_unique(array_filter(array_map(function ($item) use ($map) {
            return self::map($item->featureLongDesc, $map);
        }, $brands))));
        return !empty($brands) ? $brands : [];
    }


    /**
     * Get the location's chain
     * @return array
     */
    public function getChain()
    {
        $chain = $this->storeObject->storeChain;
        $chain = array_map(function ($store) {
            return [
                'locationName' => $store->locationName,
                'locationCode' => intval($store->locationCode),
            ];
        }, $chain);
        return $chain;
    }

    /**
     * Format a phone number in the US-style
     *
     * @param $number
     * @return string
     */
    private function phoneNumberfy($number)
    {
        if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', (string)$number, $matches)) {
            return sprintf('(%s) %s-%s', $matches[1], $matches[2], $matches[3]);
        }
        return (string)$number;
    }
}
