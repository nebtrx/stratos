<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use Symfony\Component\Yaml\Yaml;

class ConfigManager extends Singleton implements ArrayAccess
{
    const CONFIG_FILE_NAME = "settings.yml"; 
    
    private 
        $container,
        $read_only_entries;
    
    protected final function initialize() 
    {
        parent::initialize();
        $this->container = new Pimple();
        $config_parsed = Yaml::parse($this->getConfigDir()."/".self::CONFIG_FILE_NAME);
        
        foreach ($config_parsed as $section => &$section_config)
        {
            if ($section === "http_scrap") 
            {
                $section_config['next_scrapping_agent'] = function() use (&$section_config)
                {
                    // si existe entradas en el arreglo agents
                    if (isset($section_config['agents']) && count($section_config['agents'] > 0))
                    {
                        // si agent-swapping = TRUE
                        if ($section_config['agent_swapping'] ) 
                        {
                            // retorno uno aleatorio                                
                            $r = mt_rand(0,(int) count($section_config['agents'])-1);
                            return $section_config['agents'][$r];
                        }
                        // sino el primero es el default
                        return $section_config['agents'][0];
                    }
                    // sino devuelve la entrada ultradefaul
                    return "WEB-BOT";
                };
            }
            
            $this->container[$section] = $section_config;            
            $this->read_only_entries[] = $section;
        }
    }
    
    /**
     * Retrieves the fully resolved path to config dir
     * 
     * @return string   Fully resolved path to the config dir 
     */
    public function getConfigDir()
    {
        return realpath(__DIR__."/../../config");
    }
    
    /**
     * Retrieves the fully resolved path to base dir
     * 
     * @return string   Fully resolved path to the base dir 
     */
    public function getBaseDir()
    {
        return realpath(__DIR__."/../..");
    }
    
    /**
     * Retrieves the fully resolved path to log dir
     * 
     * @return string   Fully resolved path to the log dir 
     */
    public function getLogDir()
    {
        return realpath(__DIR__."/../../log");
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
        if (array_key_exists($offset, $this->read_only_entries))
        {
            throw new ConfigurationAccessException(103);
        }  
        $this->container[$offset] = $value;
    }
    
    public function offsetUnset($offset) 
    {
        if (array_key_exists($offset, $this->read_only_entries))
        {
            throw new ConfigurationAccessException(103);
        }  
        unset ($this->container[$offset]);
    }
}

