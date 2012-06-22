<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HttpScraperLogger
 *
 * @author neb
 */
class HttpScraperLogger extends Logger implements IStartup
{
    private        
        $file_name;
    
    /**
     * Initializes this logger.     *
     *
     * @param array             $options    An array of options.
     */
    public function initialize($options = array()) 
    {   
        parent::initialize($options);
        
        if (!isset($options['file_name'])) 
        {
            throw new StartupInitParamsExceptionException(get_class($this), 'file_name');
        }         
        if (!isset($options['format'])) 
        {
            throw new StartupInitParamsExceptionException(get_class($this), 'format');
        }                
        $this->format = $options['format'];
        $this->file_name = ConfigManager::bind()->getBaseDir().'/log/'.$options['file_name'];
        $this->event_dispatcher->connect('http_scraper.log', array($this, 'HttpScrapLogEventHandler')); 
        
    }    
   

    /**
     * Listens to http_scraper.log events.
     *
     * @param Event $event            An sfEvent instance
     */    
    public function HttpScrapLogEventHandler(Event $event) 
    {       
        $context = Context::bind();
        switch ($event['method']) 
        {
            case HttpScraper::GET_METHOD:
                $method = 'GET';
                break;
            case HttpScraper::HEAD_METHOD:
                $method = 'HEAD';
                break;
            case HttpScraper::POST_METHOD:
                $method = 'POST';
                break;
        }
        
        $log_entry = strtr($this->format, array(
            '%time%'                => strftime($this->time_format, $event->getTime()),
            '%webbot_finger_print%' => $context['webbot']->getFingerPrint(),
            '%http_code%'           => $event['http_code'],
            '%method%'              => $method,
            '%url%'                 => $event['url'],
            '%agent%'               => $event['agent'],  
            '%referer%'             => $event['referer'],
            '%EOL%'                 => PHP_EOL
        ));
        $this->log($log_entry);
    }    
    
    
    /**
     * Logs a message.
     *
     * @param string $message   Message
     */
    protected function doLog($message)
    {
        $this->log2File($this->file_name, $message);
    }
}
