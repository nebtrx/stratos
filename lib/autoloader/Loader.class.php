<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Loader
 *
 * @author dabreus
 */

require __DIR__.'/../../lib/singleton/Singleton.class.php';


class Loader extends Singleton
{
    const CLASS_ONLY_MODE = 0;
    
    const NAMESCPACE_CLASS_MODE = 1;

    protected 
        $class_files,
        $class_ditectories,
        $mode,
        $base_dir; 

    public function loadClass($class) 
    {   
        if ($class_file = $this->getClassFile($class))
        {            
            //echo $class_file.PHP_EOL;
            require $class_file;
        }
    }

    public function register($mode = 0, $prepend = false)
    {
        $this->mode = $mode;
        if ($mode == self::CLASS_ONLY_MODE) 
        {
            $this->initClassOnlyMode();
        }
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function getClassFile($class) 
    {
        switch ($this->mode) 
        {
            case self::CLASS_ONLY_MODE:
            {
                // in case of namespaces
                if (strrpos($class, '\\')) 
                {
                    $class = substr($class, strrpos($class, '\\') + 1);
                }
                if (!isset($this->class_files[$class])) 
                {
                    return null;
                }
                return $this->base_dir . '/' . $this->class_files[$class];
                //break;
            }
            case self::NAMESCPACE_CLASS_MODE:
            {
                throw new StratosException(999);
                //break;
            }
        }               
    }
    
    protected function initialize() 
    {        
        parent::initialize();          
        $this->base_dir = realpath(__DIR__.'/../..');        
    }
    
    private function initClassOnlyMode()
    {
        $this->class_files = array();
        $this->class_ditectories = array(    
            $this->base_dir.'/bot',
            $this->base_dir.'/lib/background_execution',
            $this->base_dir.'/lib/config_manager',
            $this->base_dir.'/lib/context',            
            $this->base_dir.'/lib/event_dispatcher',
            $this->base_dir.'/lib/exception',
            $this->base_dir.'/lib/logger',            
            $this->base_dir.'/lib/pimple',
            $this->base_dir.'/lib/scraper',
            $this->base_dir.'/lib/singleton',
            $this->base_dir.'/lib/startup',
            $this->base_dir.'/lib/yaml',
            $this->base_dir.'/model',
        );
                
        $this->resolve();
        //var_dump($this->class_files);
    }
    
    private function resolve()
    {
        foreach ($this->class_ditectories as $class_dir) 
        {            
            $this->resolveClassFiles($class_dir);
        }
    }
    
    private function resolveClassFiles($path)
    {
        if (is_dir($path)) 
        {
            $folders_and_files = scandir($path);
            $entries = array_slice($folders_and_files, 2);
            foreach ($entries as $entry)
            {
                $this->resolveClassFiles($path.'/'.$entry);
            }
            
        }
        else
        {
            if (strpos($path, '.php') == strlen($path)-4 ) 
            {
                $class = basename($path, false === strpos($path, '.class.php') ? '.php' : '.class.php');
                $class_path = str_replace($this->base_dir.'/', '', $path);
                $this->class_files[$class] = $class_path;
            }
            
        }
    }
}