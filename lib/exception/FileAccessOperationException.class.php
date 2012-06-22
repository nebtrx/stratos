<?php

/**
 * Exception raised during during troubles accessing any value of configuration
 *
 * @author     neb
 */
class FileAccessOperationException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($filename, $previous = NULL)
    {   
        parent::__construct(107, array('file' => $filename), $previous);
    } 
}

