
<?php

/**
 * Exception raised while parsing content from a downloaded page
 *
 * @author     neb
 */
class ParseWebbotException extends WebbotExecutionException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($code = 108, $message_params = array(), $previous = NULL)
    {   
        parent::__construct($code, $message_params, $previous);
    } 
}

