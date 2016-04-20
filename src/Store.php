<?php namespace FSM\Ace;

use GuzzleHttp\Client;
use Exception;

class Store
{
    const ACE_URL_FORMAT = "http://www.acehardware.com/storeLocServ?heavy=true&token=ACE&operation=storeData&storeID=%05d";
    private $cacheFolder = null;
    private $storeNumber = null;
    private $url = null;
    private $body = null;
    private $storeObject = null;

    private $locationName = null;
    private $address = null;
    private $postalCode = null;
    private $stateCode = null;
    private $city = null;
    private $phoneNumber = null;
    private $longitude = null;
    private $latitude = null;
    private $storeInfoURL = null;
    private $hours = null;
    private $departments = null;
    private $services = null;
    private $brands = null;
    private $owner = null;
    private $staff = null;
    private $storeBiography = null;



    /**
     * Initialize a Store object
     *
     * Will use a cache folder if it's writeable, specified either in the constructor or
     * in the constant `FSM_ACE_CACHEFOLDER`
     *
     * @param int    $storeNumber The store number
     * @param string $cacheFolder Path to the cache folder
     */
    public function __construct(int $storeNumber, $cacheFolder = null)
    {
        $this->cacheFolder = $cacheFolder;
        
        if (is_null($this->cacheFolder) && defined('FSM_ACE_CACHEFOLDER')) {
            $this->cacheFolder = FSM_ACE_CACHEFOLDER;
        }

        $this->storeNumber = $storeNumber;
        $this->url = sprintf(self::ACE_URL_FORMAT, $storeNumber);
        $this->initialize();
    }



    /**
     * Load the store object (remotely or from cache)
     */
    private function initialize()
    {
        $cachePath = $this->cacheFolder . '/' . $this->storeNumber . '.json';
        if (is_null($this->cacheFolder) || !is_readable($cachePath)) {
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
        if (is_null($this->locationName)) {
            $this->locationName = $this->storeObject->locationName;
        }
        return $this->locationName;
    }



    /**
     * Get the location's address
     * @return string
     */
    public function getAddress()
    {
        if (is_null($this->address)) {
            $i = 1;
            while (property_exists($this->storeObject, "address{$i}")) {
                $this->address[] = $this->storeObject->{"address{$i}"};
                $i++;
            }
        }
        return implode(',', $this->address);
    }



    /**
     * Get the location's phone number
     * @return string
     */
    public function getPostalCode()
    {
        if (is_null($this->postalCode)) {
            $this->postalCode = $this->storeObject->postalCode;
        }
        return $this->postalCode;
    }



    /**
     * Get the location's state
     * @return string
     */
    public function getStateCode()
    {
        if (is_null($this->stateCode)) {
            $this->stateCode = $this->storeObject->stateCode;
        }
        return $this->stateCode;
    }



    /**
     * Get the location's state
     * @return string
     */
    public function getCity()
    {
        if (is_null($this->city)) {
            $this->city = $this->storeObject->city;
        }
        return $this->city;
    }



    /**
     * Get the location's phone number
     * @return string
     */
    public function getPhoneNumber()
    {
        if (is_null($this->phoneNumber)) {
            $this->phoneNumber = $this->storeObject->phoneNumber;
        }
        return $this->phoneNumber;
    }



    /**
     * Get the location's URL
     * @return string
     */
    public function getStoreInfoURL()
    {
        if (is_null($this->storeInfoURL)) {
            $this->storeInfoURL = (!empty($this->storeObject->storeInfoURL)) ? $this->storeObject->storeInfoURL : sprintf("http://www.acehardware.com/mystore/index.jsp?store=%05d", $this->storeNumber);
        }
        return $this->storeInfoURL;
    }



    /**
     * Get the location's biography
     * @return string
     */
    public function getStoreBiography()
    {
        if (is_null($this->storeBiography)) {
            $this->storeBiography = $this->storeObject->storeBiography;
        }
        return $this->storeBiography;
    }



    /**
     * Get the location's owner
     * @return string
     */
    public function getOwner()
    {
        if (is_null($this->owner)) {
            $owner = $this->storeObject->owner;
            $this->owner = array_reduce($owner, function ($carry, $item) {
                $carry .= (!empty($carry)) ? ", {$item->fullName}" : $item->fullName;
                return $carry;
            });
        }
        return $this->owner;
    }



    /**
     * Get the location's staff
     * @return array
     */
    public function getStaff()
    {
        if (is_null($this->staff)) {
            $staff = $this->storeObject->storeStaff;
            $this->staff = array_map(function ($person) {
                return [
                    'fullName' => $person->fullName,
                    'personTitle' => $person->personTitle,
                    'personImageUrl' => $person->personImageUrl,
                ];
            }, $staff);
        }
        return $this->staff;
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
        if (is_null($this->latitude) || is_null($this->longitude)) {
            $this->latitude = $this->storeObject->latitude;
            $this->longitude = $this->storeObject->longitude;
        }
        return [
            $this->latitude,
            $this->longitude,
        ];
    }



    /**
     * Get the location's hours
     * @return array Hours by day
     */
    public function getHours()
    {
        if (is_null($this->hours)) {
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

            $this->hours = [
                'Monday' => $hours["openingTimeMon"] . ' - ' . $hours['closingTimeMon'],
                'Tuesday' => $hours["openingTimeTue"] . ' - ' . $hours['closingTimeTue'],
                'Wednesday' => $hours["openingTimeWed"] . ' - ' . $hours['closingTimeWed'],
                'Thursday' => $hours["openingTimeThu"] . ' - ' . $hours['closingTimeThu'],
                'Friday' => $hours["openingTimeFri"] . ' - ' . $hours['closingTimeFri'],
                'Saturday' => $hours["openingTimeSat"] . ' - ' . $hours['closingTimeSat'],
                'Sunday' => $hours["openingTimeSun"] . ' - ' . $hours['closingTimeSun'],
            ];
        }
        return $this->hours;
    }



    /**
     * Return a different value for a key based on a mapping array
     * @param  string|int   $value Original value
     * @param  array        $map   Map of values (`'original' => 'new'`)
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
        if (is_null($this->departments)) {
            $departments = array_merge(
                is_array($this->storeObject->departments) ? $this->storeObject->departments : [],
                is_array($this->storeObject->customDepartments) ? $this->storeObject->customDepartments : []
            );

            $map = Config::get('map', 'departments');

            $this->departments = array_unique(array_filter(array_map(function ($item) use ($map) {
                return self::map($item->featureLongDesc, $map);
            }, $departments)));
        }
        return $this->departments;
    }



    /**
     * Get the location's services
     * @return array Services
     */
    public function getServices()
    {
        if (is_null($this->services)) {
            $services = array_merge(
                is_array($this->storeObject->standardServices) ? $this->storeObject->standardServices : [],
                is_array($this->storeObject->customServices) ? $this->storeObject->customServices : []
            );

            $map = Config::get('map', 'services');

            $this->services = array_unique(array_filter(array_map(function ($item) use ($map) {
                return self::map($item->featureLongDesc, $map);
            }, $services)));
        }
        return $this->services;
    }



    /**
     * Get the location's brands
     * @return array Brands
     */
    public function getBrands()
    {
        if (is_null($this->brands)) {
            $brands = array_merge(
                is_array($this->storeObject->specialtyBrands) ? $this->storeObject->specialtyBrands : [],
                is_array($this->storeObject->customSpecialtyBrand) ? $this->storeObject->customSpecialtyBrand : []
            );

            $map = Config::get('map', 'brands');

            $this->brands = array_unique(array_filter(array_map(function ($item) use ($map) {
                return self::map($item->featureLongDesc, $map);
            }, $brands)));
        }
        return $this->brands;
    }



    /**
     * Get the location's chain
     * @return array
     */
    public function getChain()
    {
        if (is_null($this->chain)) {
            $chain = $this->storeObject->storeChain;
            $this->chain = array_map(function ($store) {
                return [
                    'locationName' => $store->locationName,
                    'locationCode' => intval($store->locationCode),
                ];
            }, $chain);
        }
        return $this->chain;
    }
}
