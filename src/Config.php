<?php namespace FootstepsMarketing\Ace;

use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Config handles the loading and access of the configuration.
 * @package FootstepsMarketing\Ace
 */
class Config
{
    /**
     * $config contains the configuration array.
     * @var array
     */
    private static $config;

    /**
     * get retrieves a configuration path given arguments in a 'tree' sort of format.
     *
     * Given configuration YAML like so:
     * ```yaml
     * map:
     *   exclusive: true
     * ```
     *
     * get is used in this manner:
     * ```php
     * Config::get('map', 'exclusive'); // true
     * ```
     *
     * This avoids issues with attempting to access non-existent
     * array keys -- get simply returns null.
     *
     * @param array ...$args
     * @return mixed
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

    /**
     * initialize loads the config either from a string or a file as needed.
     */
    private static function initialize()
    {
        if (!is_null(self::$config)) {
            return;
        }
        if (defined('FSM_ACE_CONFIG_STRING')) {
            self::initializeWithString(FSM_ACE_CONFIG_STRING);
            return;
        }
        if (defined('FSM_ACE_CONFIG_PATH')) {
            self::initializeWithFilePath();
            return;
        }
    }

    /**
     * initializeWithString parses a string containing YAML
     * to load the configuration.
     * @param $yamlString
     */
    private static function initializeWithString($yamlString)
    {
        $parser = new YamlParser();
        self::$config = $parser->parse($yamlString);
    }

    /**
     * initializeWithFilePath reads a file containing YAML
     * configuration and loads it.
     */
    private static function initializeWithFilePath()
    {
        $configPath = (defined('FSM_ACE_CONFIG_PATH')) ? FSM_ACE_CONFIG_PATH : __DIR__ . '/config.yaml';
        self::initializeWithString(file_get_contents($configPath));
    }
}
