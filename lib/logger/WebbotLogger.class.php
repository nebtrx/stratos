<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebbotLogger
 *
 * @author neb
 */
class WebbotLogger extends Logger implements IStartup
{
    protected         
        $file_name;
    
    /**
     * Initializes this logger.
     *
     * @param sfEventDispatcher $dispatcher A sfEventDispatcher instance
     * @param array             $options    An array of options.
     */
    public function initialize($options = array()) 
    {   
        parent::initialize($options);
        $this->event_dispatcher->connect('webbot.log', array($this, 'WebbotLogEventHandler'));
        $this->event_dispatcher->connect('context.webbot_injected', array($this, 'ContextWebbotInjectedEventHandler')); 
        
    }       
    
    /**
     * Listens to context.webbot_injected events.
     *
     * @param sfEvent $event An sfEvent instance
     */
    public function ContextWebbotInjectedEventHandler(Event $event) 
    {           
        $context = $event->getSender();
        $this->file_name = ConfigManager::bind()->getBaseDir().'/log/'.$context['webbot']->getFingerPrint().'.log';
    }
    
    /**
     * Listens to webbot.log events.
     *
     * @param sfEvent $event An sfEvent instance
     */
    public function WebbotLogEventHandler(Event $event) 
    {            
        $log_entry = strtr($this->format, array(
              '%sender%'   => get_class($event->getSender()),
              '%message%'  => $event['message'],
              '%time%'     => strftime($this->time_format, $event->getTime()),
              '%EOL%'      => PHP_EOL
        ));
        $this->log($log_entry);
    }    
    
    
    /**
     * Logs a message.
     *
     * @param string $message   Message
     * @param string $priority  Message priority
     */
    protected function doLog($message)
    {              
        $this->log2File($this->file_name, $message);
    }
}
