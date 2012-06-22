
<?php

/**
 * Exception raised while page download failes
 *
 * @author     neb
 */
class PageDownloadWebbotExecutionException extends WebbotExecutionException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($code = 112, $message_params = array(), $previous = NULL)
    {   
        parent::__construct($code, $message_params, $previous);
    } 
}

