<?php

/**
 * ExceptionLogger logs all the system's raised exceptions to a custom log file.
 * 
 * @subpackage Exception
 * @author     neb
 */
class ExceptionLogger extends Logger implements IStartup 
{
     private        
        $file_name,
        $u_file_name,
        $inner_format;
    
    /**
     * Initializes this logger.
     *
     * @param sfEventDispatcher $dispatcher A sfEventDispatcher instance
     * @param array             $options    An array of options.
     */
    public function initialize($options = array()) 
    {    
        parent::initialize($options);           
        
        if (!isset($options['file_name'])) 
        {
            throw new StartupInitParamsExceptionException(get_class($this), 'file_name');
        }   
//        if (!isset($options['u_file_name'])) 
//        {
//            throw new StartupInitParamsExceptionException(get_class($this), 'u_file_name');
//        }
        if (!isset($options['format'])) 
        {
            throw new StartupInitParamsExceptionException(get_class($this), 'format');
        }
        if (!isset($options['inner_format'])) 
        {
            throw new StartupInitParamsExceptionException(get_class($this), 'inner_format');
        }
        $this->format = $options['format'];
        $this->inner_format = $options['inner_format'];
        $this->file_name = ConfigManager::bind()->getBaseDir().'/log/'.$options['file_name'];
        $this->u_file_name = ConfigManager::bind()->getBaseDir().'/log/'.$options['u_file_name'];
        $this->event_dispatcher->connect('application.log_exception', array($this, 'ApplicationLogExceptionEventHandler'));
    }
     

    /**
     * Handles the uncaptured exception
     *
     * @param Event $exception      An Event instance
     */
    public function UnhandledExceptionEventHandler(Exception $exception) 
    {
        $context = Context::bind();        
        $log_entry = strtr($this->format, array(
            '%time%'                => strftime($this->time_format, time()),
            '%webbot_finger_print%' => $context['webbot']->getFingerPrint(),
            '%type%'                => get_class($exception),
            '%message%'             => $exception->getMessage(),
            '%file%'                => $exception->getFile(),           
            '%line%'                => $exception->getLine(),
            '%EOL%'                 => PHP_EOL
        ));
        $log_entry .= PHP_EOL;
        
        $this->log2File($this->u_file_name, $log_entry);
    }
            
    /**
     * Listens to application.log_exception events.
     *
     * @param Event $event      An Event instance
     */
    public function ApplicationLogExceptionEventHandler(Event $event) 
    {
        $context = Context::bind();        
        $log_entry = strtr($this->format, array(
            '%time%'                => strftime($this->time_format, $event->getTime()),
            '%webbot_finger_print%' => $context['webbot']->getFingerPrint(),
            '%type%'                => $event['type'],
            '%message%'             => $event['message'],
            '%file%'                => $event['file'],           
            '%line%'                => $event['line'],
            '%EOL%'                 => PHP_EOL
        ));
        
        if ($event->getSender()->getPrevious()!== NULL)
        {
            $log_entry .= strtr($this->inner_format, array(
                '%time%'                => strftime($this->time_format, $event->getTime()),
                '%webbot_finger_print%' => $context['webbot']->getFingerPrint(),
                '%inner_type%'          => isset ($event['inner_type'])? $event['inner_type']: NULL,
                '%inner_message%'       => isset ($event['inner_message'])? $event['inner_message']: NULL,
                '%inner_file%'          => isset ($event['inner_file'])? $event['inner_file']: NULL,            
                '%inner_line%'          => isset ($event['inner_line'])? $event['inner_line']: NULL,           
                '%EOL%'                 => PHP_EOL
            ));
        }
        $log_entry .= PHP_EOL;
        
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