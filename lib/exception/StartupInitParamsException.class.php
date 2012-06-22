<?php

/**
 * Exception raised loading the init params/options of a startup
 *
 * @author     neb
 */
class StartupInitParamsExceptionException extends ContextStartupException
{    
    /**
     * Constructs the exception.
     *         
     * @param  $message         Message
     * @param  $previous        Previous exception. 
     */     
    public function  __construct($startup_name, $param, $previous = NULL)
    {   
        parent::__construct(105, array('startup' => $startup_name, 'parameter' => $param), $previous);
    } 
}

