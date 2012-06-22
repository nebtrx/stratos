<?php

/**
 * Exception raised during during troubles accessing any value of configuration
 *
 * @author     neb
 */
class ConfigurationAccessException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($code = 101, $message_params = array(), $previous = NULL)
    {   
        parent::__construct($code, $message_params, $previous);
    } 
}

