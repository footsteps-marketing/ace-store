<?php
require __DIR__ . '/vendor/autoload.php';
require 'Store.php';
require 'Config.php';

define('FSM_ACE_CACHEFOLDER', __DIR__ . '/cache');
define('FSM_ACE_CONFIGPATH', __DIR__ . '/config.yaml');

foreach ([5784, 6774, 14666, 15421, 15693, 15791, 15916, 16046, 16307] as $storeNum) {
    $store = new FSM\Ace\Store($storeNum);
    echo sprintf("%s\n", implode("", [
        array_reduce($store->getDepartments(), function ($carry, $item) {
            return $carry . sprintf("/Departments/%s;", $item);
        }),
        array_reduce($store->getServices(), function ($carry, $item) {
            return $carry . sprintf("/Services/%s;", $item);
        }),
    ]));
}
