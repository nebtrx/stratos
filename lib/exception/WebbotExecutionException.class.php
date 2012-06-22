
<?php

/**
 * Exception raised during the execution of a webbot
 *
 * @author     neb
 */
class WebbotExecutionException extends StratosException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($code = 109, $message_params = array(), $previous = NULL)
    {   
        parent::__construct($code, $message_params, $previous);
    } 
}

