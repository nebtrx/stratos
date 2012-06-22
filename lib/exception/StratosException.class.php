<?php

/**
 * Base system exception for all the custom exceptions defined in the system.
 * Contains the common needed behavior to log the exception events.
 * 
 * @author     Neb
 */
class StratosException extends Exception
{
    
    /**
     * Constructs the base system exception.
     *         
     * @param  $previous       Previous exception.  
     */    
    public function  __construct($code = -1, $message_params = array(), $previous = NULL)
    {  
        $message = NULL;
        if ($code !== -1) 
        {
            $params = array_keys($message_params);
            foreach ($params as &$param) 
                $param = '%'.$param.'%';
            $escaped_params = array_combine($params, $message_params);
            
            $message = strtr(ErrorMapper::mapExceptionCode($code), $escaped_params);            
        }
        //$message = ($code !== -1)?call_user_func_array('sprintf', array_merge((array)ErrorMapper::mapExceptionCode($code), $message_params)):NULL;       
        parent::__construct($message, $code, $previous);
        
        $config = ConfigManager::bind();       
        if ($config['general']['error_handling']['exception_logging'])
        { 
            $this->notifyException();
        }                
    }
    
    /**
     * Unleashed the routine for loggin the exception
     * 
     */
    private function notifyException()
    {   
        $parameters = array(            
            "type"              => get_class($this),
            "message"           => $this->getMessage(),
            "file"              => $this->file,
            "line"              => $this->line,
        );
        
        if($this->getPrevious() !== NULL )
        {      
            $parameters["inner_type"]        = get_class($this->getPrevious());
            $parameters["inner_message"]     = $this->getPrevious()->getMessage();
            $parameters["inner_file"]        = $this->getPrevious()->getFile();            
            $parameters["inner_line"]        = $this->getPrevious()->getLine();
        }
        
        $dispatcher = EventDispatcher::bind();
        // notifying exception                   
        $dispatcher->notify(new Event($this, 'application.log_exception', $parameters));
    }
}