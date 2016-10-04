<?php namespace FootstepsMarketing\Ace;

use Symfony\Component\Yaml\Parser as YamlParser;

class Config
{
    private static $config = null;

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

    private static function initializeWithString($yamlString)
    {
        $parser = new YamlParser();
        self::$config = $parser->parse($yamlString);
    }

    private static function initializeWithFilePath()
    {
        $configPath = (defined('FSM_ACE_CONFIG_PATH')) ? FSM_ACE_CONFIG_PATH : __DIR__ . '/config.yaml';
        self::initializeWithString(file_get_contents($configPath));
    }
}
