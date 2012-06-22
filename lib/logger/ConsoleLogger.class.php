<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConsoleLogger
 *
 * @author neb
 */
class ConsoleLogger extends Logger implements IStartup
{  
    /**
     * Initializes this logger.
     *
     * @param sfEventDispatcher $dispatcher A sfEventDispatcher instance
     * @param array             $options    An array of options.
     */
    public function initialize($options = array()) 
    {   
        parent::initialize($options);
        $this->event_dispatcher->connect('webbot.log', array($this, 'ConsoleLogEventHandler')); 
    }
        
   

    /**
     * Listens to webbot.log events.
     *
     * @param sfEvent $event An sfEvent instance
     */
    public function ConsoleLogEventHandler(Event $event) 
    {         
        $log_entry = strtr($this->format, array(
              '%sender%'   => get_class($event->getSender()),
              '%message%'  => $message,
              '%time%'     => strftime($this->time_format, $event->getTime()),
              '%EOL%'      => PHP_EOL
        ));
        $this->log($logEntry);
    }    
    
    
    /**
     * Logs a message.
     *
     * @param string $message   Message
     */
    protected function doLog($message)
    {
        $this->log2Console($message);
    }    
}