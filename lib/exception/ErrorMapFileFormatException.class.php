<?php

/**
 * Exception raised while error map file is bad formated
 *
 * @author     neb
 */
class ErrorMapFileFormatException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($message = "Error mapping error: Error map file bad formated")
    {   
        parent::__construct(-1, $previous);
        $this->message = $message;
    } 
}

