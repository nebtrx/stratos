<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebBot
 *
 * @author neb
 */

abstract class Webbot 
{
    private 
        $instance_identifier,
        $stop_condition_adquired,
        $stop_condition_function,
        $primary_url_target;
            
    protected 
        $config,
        $event_dispatcher;    

    public function __construct($options) 
    {
        $this->initialize($options);        
    }

    /**
     * For initial settings purposes
     */
    protected function initialize($options)
    {
        $this->config = ConfigManager::bind();
        $this->event_dispatcher = EventDispatcher::bind();
        $this->primary_url_target = $options['url_target'];
        $this->instance_identifier = $options['instance_identifier'];
        $this->stop_condition_adquired = FALSE;
        
        $this->event_dispatcher->connect('context.webbot_injected', array($this, 'ContextWebbotInjectedEventHandler'));
        //$this->event_dispatcher->connect('webbot.stop_condition_adquired', array($this, 'WebbotStopConditionAdquiredEventHandler'));
    }
    
    /**
     * Listens to context.webbot_injected events. This methods must be override
     * in the child class
     *
     * @param sfEvent $event An sfEvent instance
     */
    public function ContextWebbotInjectedEventHandler(Event $event){}
    
    
    /**
     * Returns the primary Target
     */
    public function getPrimaryUrlTarget()
    {
        return $this->primary_url_target;
    }
    
    /**
     * Sets the primary Target
     */
    public function setPrimaryUrlTarget($url_target)
    {
        $this->primary_url_target = $url_target;
    }
    
    /**
     * Returns the Instance Id
     */
    public function getInstanceIdentifier()
    {
        return $this->instance_identifier;
    }
    
    /**
     * Sets the primary Target
     */
    public function setInstanceIdentifier($instance_identifier)
    {
        $this->instance_identifier = $instance_identifier;
    }
    
    /**
     * Returns the foot print of the bot composed by its class name and its
     * instance ID.
     */
    public function getFingerPrint()
    {
        return strtolower(get_class($this).':'.$this->instance_identifier);
    }
    
    /**
     * Sets a closure for evaluate if the stop conditions happens
     * 
     * @param Closure $evaluation_function 
     */
    public function setStopConditionFunction(Closure $evaluation_function)
    {
        if (!$evaluation_function instanceof Closure)
        {
            throw new WebbotExecutionException(109);
        }
        $this->stop_condition_function = $evaluation_function;
    }
    
    /**
     * Evaluates the stop condition function
     * 
     * @param   array   $env_vars   Array of enviroment vars passed to the stop condition function
     * @return  boolean             TRUE is stop condition is happening
     *                              FALSE otherwise         
     */
    public function evaluateStopConditionAdquired($env_vars = array())
    {
        if (!$this->stop_condition_adquired) 
        {
            if (!isset($this->stop_condition_function))
            {
                throw new WebbotExecutionException(110);
            }        
            if (!$this->stop_condition_function instanceof Closure)
            {
                throw new WebbotExecutionException(111);
            }
            $callable = $this->stop_condition_function;
            if ($callable($env_vars)) 
            {
                $this->raisedStopConditionAdquired($env_vars);
            }
        }
        
        return $this->stop_condition_adquired;
    }
    
    /**
     * Verifies if stop condition was adquired
     * 
     * @return boolean
     */
    public function isStopConditionAdquired()
    {
        return $this->stop_condition_adquired;
    }


    /**
     * Raise the stop condition adquired
     * 
     * @param array $env_vars        Array of environment vars passed to the method
     */
    protected function raisedStopConditionAdquired($env_vars = array())
    {
        $this->stop_condition_adquired = TRUE;
        $this->logActivity('Webbot stop condition adquired.');
        $this->event_dispatcher->notify(new Event($this, 'webbot.stop_condition_adquired'), $env_vars);
        $this->processStopConditionAdquired($env_vars);
    }

    /**
     * Custom meohtd implemented by child clases which executes the corresponding 
     * for adquirin the stop condition
     * 
     * @param type $env_vars        Array of environment vars passed to the method
     */
    protected function processStopConditionAdquired($env_vars = array()){}
    
    /**
     * Custom method implemented by child clases which starts the executio of the
     * webbot
     */
    public abstract function doExecute();
    
    /**
     * Executes the webbot
     */
    public function execute()
    {
        $this->logActivity('Webbot execution started');
        $this->doExecute();
        $this->logActivity('Webbot execution finished');
    }
    
    /**
     * Extract links from given downloaded page
     * 
     * @param string $downloaded_page Downloaded page array containing all the 
     *                                needed data
     * @param string $open_tag        Links open tags
     * @param string $close_tag       Links close tags
     * @return array                  Array of links
     */
    protected function extractLinks($downloaded_page, $open_tag, $close_tag)
    {              
        $links = array();
        # Get page base for $url
        $page_base = Resolver::getBasePageAddress($downloaded_page['STATUS']['url']);
        if ($this->config['webbot']['base_domain_relative_links'] == TRUE) 
        {
            $page_base = Resolver::getBaseDomainAddress($page_base);
        }
            
        // esto se puede pponer en una configuracion
        $anchor_tags = HtmlParser::parse2Array($downloaded_page['FILE'], $open_tag, $close_tag);        
        # Put http attributes for each tag into an array
        for ($i = 0; $i < count($anchor_tags); $i++) 
        {
            $href = HtmlParser::getAttribute($anchor_tags[$i], "href");
            //echo $links[$i]."<br>";
            $resolved_addres = Resolver::resolveAddress($href, $page_base);
            $links[] = $resolved_addres;
            //logging
            $this->logActivity("Harvested: " . $resolved_addres);
        }
        return $links;   
    }
    
    /**
     * Parses links of a given url page to donwload
     * 
     * @param string $url             Page Url to donwload and harvest links
     * @param string $referer         Url referer to register when donwloading page
     * @param string $open_tag        Links open tags
     * @param string $close_tag       Links close tags
     * @return array                  Array of links
     */
    protected function harvestLinks($url, $referer, $open_tag, $close_tag)
    {              
        $links = array();
        # Get page base for $url
        $page_base = Resolver::getBasePageAddress($url);
        if ($this->config['webbot']['base_domain_relative_links'] == TRUE) 
        {
            $page_base = Resolver::getBaseDomainAddress($page_base);
        }
        
        # Download webpage        
        $downloaded_page = $this->downloadPage($url, $referer);
            
        // esto se puede pponer en una configuracion
        $anchor_tags = HtmlParser::parse2Array($downloaded_page['FILE'], $open_tag, $close_tag);        
        # Put http attributes for each tag into an array
        for ($i = 0; $i < count($anchor_tags); $i++) 
        {
            $href = HtmlParser::getAttribute($anchor_tags[$i], "href");
            //echo $links[$i]."<br>";
            $resolved_addres = Resolver::resolveAddress($href, $page_base);
            $links[] = $resolved_addres;
            //logging
            $this->logActivity("Harvested: " . $resolved_addres);
        }
        return $links;   
    }
    
    /**
     * Downloads a page
     *
     * @param type $url
     * @param type $referer
     * @return type 
     */
    protected function downloadPage($url, $referer)
    {
        # Download webpage        
        $downloaded_page = HttpScraper::getHttp($url, $referer);
        // logging
        $this->logActivity("Downloaded: ".$downloaded_page['STATUS']['url']);
        //var_dump($downloaded_page);die;
        if ($downloaded_page['STATUS']['http_code'] < 200 || $downloaded_page['STATUS']['http_code'] >= 400)
        {   
            $curl_error_info = '';
            if (isset ($downloaded_page['ERROR']) && $downloaded_page['ERROR'] != "")
            {
                $curl_error_info = 'CURL Error Information: '.$downloaded_page['ERROR'].'.';
            }
            
            throw new PageDownloadWebbotExecutionException(112, array(
                    'url'               => $url,
                    'http_code'         => $downloaded_page['STATUS']['http_code'],
                    'curl_error_info'   => $curl_error_info                
                ));
        }
        return $downloaded_page;
    }
    
    /**
     * Process de data of a downloaded page. This method should be overwritten
     * in the specific webbot
     * 
     * @param string $downloaded_page Content of the page
     */
    protected function processPage($downloaded_page)
    {
        // logging
        $this->logActivity('Processed : '.$downloaded_page['STATUS']['url']);
    }
    
    /**
     * Used to log the activities of the webbot
     * 
     * @param string $message   Message to log
     */
    private function logActivity($message)
    {
        $this->event_dispatcher->notify(new Event($this, 'webbot.log', array('webbot_instance' => $this->getInstanceIdentifier(), 'message' => $message)));
    }

    /**
     * Performs a daley in execution thread of a random number of seconds. This
     * function is often to keep behavior under the hood.
     */
    protected function performStrategicDelay()
    {        
        $sleep_time = mt_rand(0, (int)$this->config['webbot']['max_delay_time']);
        sleep($sleep_time);        
        // logging
        $this->logActivity(sprintf('Webbot execution delayed: "%s" seconds', $sleep_time));
    }
    /**
     * Static Factory Method
     * 
     * @param type $params 
     */
    public static function createInstance($params)
    {
        return static::StaticBuilder($params);
    }
    
    /**
     * Abstract static builder
     * 
     */
    public abstract static function StaticBuilder($params);
    
}
