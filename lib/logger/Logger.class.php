<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logger
 *
 * @author neb
 */
abstract class Logger implements IStartup
{
    protected 
        $event_dispatcher,
        $time_format,
        $format,    
        $config;

    public function __construct($options) 
    {
        $this->initialize($options);
    }
    
    public function initialize($options = array()) 
    {
        $this->config = ConfigManager::bind();
        $this->event_dispatcher = EventDisPatcher::bind();
        
        if (!isset($this->config['general']['logging']['time_format'])) 
        {
            throw new StratosException(106, array('parameter' => 'time_format'));
        }
        if (!isset($this->config['general']['logging']['default_format'])) 
        {
            throw new StratosException(106, array('parameter' => 'default_format'));
        }
        
        $this->time_format = $this->config['general']['logging']['time_format'];
        $this->format = $this->config['general']['logging']['default_format'];        
        $this->event_dispatcher->connect('application.log', array($this, 'ApplicationLogEventHandler'));
    }
    
    /**
     * Final log method. It must be implemented specificly by child class
     */
    abstract protected function doLog($message);

    /**
     *
     * @param type $message
     * @return type 
     */
    public function log($message)
    {
        $this->doLog($message);       
    }
    
    /**
     * For general log purposes
     * 
     * @param Event $event 
     */
    public function ApplicationLogEventHandler(Event $event) 
    {
        throw new StratosException(999);
    }
    
    /**
     * Logs a message to a log FILE.
     * 
     * @param type $file_name
     * @param type $message 
     */
    protected function log2File($file_name, $message)
    {                   
        $exists_file = file_exists($file_name); 
        
        if (!is_writable(dirname($file_name)) || ($exists_file && !is_writable($file_name))) 
        {
            throw new FileAccessOperationException($file_name);
        }       

        $file_handler = fopen($file_name, 'a');
        if (!$exists_file) 
        {
            chmod($file_name, 0666);
        }
        
        flock($file_handler, LOCK_EX);
        fwrite($file_handler, $message);
        flock($file_handler, LOCK_UN);    
    }
    
    /**
     * Logs a message to the console.
     *
     * @param string $message   Message     
     */
    protected function log2Console($message)
    {        
        echo $message;
    }     
}
