<?php

/**
 * Exception raised when error map code isn't mapped
 *
 * @author     neb
 */
class ErrorCodeMappingException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($message = "Error mapping error: Error map code missing.")
    {   
        parent::__construct(-1, $previous);
        $this->message = $message;
    } 
}

