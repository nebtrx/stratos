<?php

/**
 * Exception raised loading the exception logger
 *
 * @author     neb
 */
class ExceptionLoggerException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($code = 501, $message_params = array(), $previous = NULL)
    {   
        parent::__construct($code, $message_params, $previous);
    } 
}

