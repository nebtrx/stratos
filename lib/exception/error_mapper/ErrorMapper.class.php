<?php

/**
 * ExceptionErrorMapper maps the exception error messages from the exception_error_message
 *
 * @author     Neb
 */  
use Symfony\Component\Yaml\Yaml;

class ErrorMapper extends Singleton
{
    protected        
        $error_map_file,
        $exception_messages_map;
    
    protected function initialize()
    {         
        $config = ConfigManager::bind();
        $this->error_map_file = $config->getBaseDir().'/'.$config['general']['error_handling']['error_map_file'];
        if (!file_exists($this->error_map_file)) 
        {
            throw new MissingErrorMapFileException();
        }   
                
        try
        {
            $this->exception_messages_map = Yaml::parse($this->error_map_file);
        } 
        catch (ParseException $e)
        {
            throw new ErrorMapFileFormatException();                                   
        }        
        
        if (!is_array($this->exception_messages_map)) 
        {
            throw new ErrorMapFileFormatException();
        }
    }


    public static function mapExceptionCode($error_code)
    {                           
        //var_dump($errorMap);die;
        if (!key_exists($error_code, self::bind()->exception_messages_map["error"]))
        {
            throw new ErrorCodeMappingException("Error mapping error: Error map code missing: $error_code.");
        }        
        $error_message = self::bind()->exception_messages_map["error"][$error_code];
        
        return $error_message;
    }
}
