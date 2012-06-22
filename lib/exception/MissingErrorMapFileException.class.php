<?php

/**
 * Exception raised when error map file is missing
 *
 * @author     neb
 */
class MissingErrorMapFileException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($message = "Error accessing file: Unable to access <file_name>.")
    {   
        parent::__construct(-1, $previous);
        $this->message = $message;
    } 
}

