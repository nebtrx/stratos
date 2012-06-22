<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../active_record/ActiveRecord.php';

/**
 * Description of Context
 *
 * @author neb
 */
class Context extends Singleton implements ArrayAccess
{
    private 
        $container,
        $config,
        $startups;     
    
    protected function initialize()
    {        
        $this->config = ConfigManager::bind();
        // setting limit time
        set_time_limit($this->config['webbot']['time_limit']);
        
        $this->container = new Pimple(); 
        
        // initializing persistence        
        $config = $this->config;
        if ($config['general']['persistence']['use_database']) 
        {
            if (!isset ($config['general']['persistence']['connections'])) 
            {
                throw new ConfigurationAccessException(101);
            }
            if (!is_array($config['general']['persistence']['connections']) || count($config['general']['persistence']['connections']) < 1) 
            {
                throw new ConfigurationAccessException(102);
            }
            $connections = $config['general']['persistence']['connections'];
            // initializing ActiveRecord
            
            ActiveRecord\Config::initialize(function($cfg) use ($connections, $config)     
            {
                $cfg->set_model_directory($config->getBaseDir().'/'.$config['general']['persistence']['models_dir']);
                $cfg->set_connections($connections);
                // assigning as default conection the first one
                $default_con_index = array_shift(array_keys($connections));
                $cfg->set_default_connection($default_con_index);
            });
        }        
        $this->loadStartups();
    }
    
    private function loadStartups()
    {
        foreach ($this->config['general']['startups'] as $name => $spec) 
        {
            if ($spec['enabled'])
            {
                // if it finds a repeated startup it doesn't overwrite it
                if (!isset ($this->startups[$name]) ) 
                {                
                    if (!in_array("IStartup", class_implements($spec['class']))) 
                    {
                        throw new ContextStartupException(113, array('startup' => $name));
                    }                    
                    $this->startups[$name] = new $spec['class']($spec['options']);
                }
            }            
        }
    }    
    
    public function execute()
    {        
        if (!isset ($this->container['webbot']) || !is_a($this->container['webbot'], 'Webbot')) 
        {
            throw new StratosException(104);
        }
        $this['webbot']->execute();
    }
    
    public function offsetGet($offset) 
    {
        return $this->container[$offset];
    }
    
    public function offsetExists($offset) 
    {
       return isset($this->container[$offset]);
    }
    
    public function offsetSet($offset, $value) 
    {
        $this->container[$offset] = $value;
    }
    
    public function offsetUnset($offset) 
    {
        unset( $this->container[$offset]);
    }
    
    /**
     * Static Builder
     * 
     * @param Webbot $webbot
     * @return Webbot 
     */
    public static function createInstance(Webbot $webbot)
    {
        $context = self::bind();            
        $context['webbot'] = $webbot;        
        EventDispatcher::bind()->notify(new Event($context, 'context.webbot_injected'));
        return $context;
    }    
}