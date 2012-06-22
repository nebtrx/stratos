<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BackgroundExecution
 *
 * @author neb
 */

##################################
#### DO REGISTER METHOD ##########
##################################
class BackgroundExecution 
{
    /**
     * Run Application in background
     *
     * @param     unknown_type $command
     * @param     unknown_type $priority
     * @return     PID
     */
    static public function execute($command, $priority = 0)
    {
       if($priority)
           $PID = shell_exec("nohup nice -n $priority $command > /dev/null 2>&1 & echo $!");
       else
           $PID = shell_exec("nohup $command > /dev/null 2>&1 & echo $!");
       return($PID);
   }
   /**
    * Check if the Application running !
    *
    * @param     unknown_type $PID
    * @return     boolen
    */
   static public function isRunning($PID)
   {
       exec("ps $PID", $ProcessState);
       return(count($ProcessState) >= 2);
   }
   /**
    * Kill Application PID
    *
    * @param  unknown_type $PID
    * @return boolen
    */
   static public function kill($PID)
   {
       if(self::isRunning($PID)){
           exec("kill -KILL $PID");
           return true;
       }else return false;
   }
}