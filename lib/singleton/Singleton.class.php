<?php

/**
 * Class to extend if you wanna implement a Singleton Pattern. Inspired by
 * Fabien Potencier's design patterns review for PHP 5.3
 * 
 * @package    lib.utils
 * @subpackage Singleton
 * @author     Omar Ahmed <oagarcia@uci.cu>
 */
abstract class Singleton 
{
    private static $instances = array();

    final private function __construct() 
    {
        if (isset(self::$instances[get_called_class()])) {
            throw new Exception("A " . get_called_class() . " instance already exists.");
        }
        static::initialize();
    }
    
    /**
     * Sets the object's initial values and configurations
     * 
     */
    protected function initialize() { }

    /**
     * Retrieves the unique instance
     *
     */
    final public static function bind() 
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }
        return self::$instances[$class];
    }
    
    /**
     * Forbids the clonation of the unique instance
     *
     */
    final private function __clone() { }

}
