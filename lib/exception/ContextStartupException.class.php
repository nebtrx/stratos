<?php

/**
 * Exception raised during the execution of a startup
 *
 * @author     neb
 */
class ContextStartupException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($code = 113, $message_params, $previous = NULL)
    {   
        parent::__construct($code, $message_params, $previous);
    } 
}

