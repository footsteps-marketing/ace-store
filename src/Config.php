<?php namespace FSM\Ace;

use Exception;
use Symfony\Component\Yaml\Parser as YamlParser;

class Config
{
    private static $config = null;

    /**
     * Load the config
     */
    private static function initialize()
    {
        if (!is_null(self::$config)) {
            return;
        }
        $configPath = (defined('FSM_ACE_CONFIGPATH')) ? FSM_ACE_CONFIGPATH : __DIR__ . '/config.yaml';
        $parser = new YamlParser();
        self::$config = $parser->parse(file_get_contents($configPath));
    }



    /**
     * Get an option
     */
    public static function get(...$args)
    {
        self::initialize();

        $option = self::$config;

        while (count($args) > 0) {
            $arg = array_shift($args);
            if (!array_key_exists($arg, $option)) {
                return null;
            }
            $option = $option[$arg];
        }

        return $option;
    }
}
