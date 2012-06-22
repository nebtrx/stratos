<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HttpScrap
 *
 * @author neb
 */
class HttpScraper 
{
    /**
     * Method HEAD
     */
    const HEAD_METHOD = 1;
    
    /**
     * Method GET
     */
    const GET_METHOD = 2;
    
    /**
     * Method POST
     */
    const POST_METHOD = 3;
    
    /**
     * EXCLUDE header
     */
    const HEADER_EXCLUDED = FALSE;
    
    /**
     * INCLUDE header
     */
    const HEADER_INCLUDED = TRUE;  
          
    /**
     * Downloads an ASCII file without the http header.
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @return  array               $return_array['FILE']   = Contents of fetched file,
     *                              $return_array['STATUS'] = CURL generated status of transfer,
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function getHttp($target, $referer)
    {
        return self::scrapHttp($target, $referer, $method = self::GET_METHOD, $data_array = "", self::HEADER_EXCLUDED);
    }
    
    
    /**
     * Downloads an ASCII file with the http header.
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @return  array               $return_array['FILE']   = Contents of fetched file, will also
     *                              include the HTTP header if requested
     *                              $return_array['STATUS'] = CURL generated status of transfer
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function getHttpWithHeader($target, $referer) 
    {
        return self::scrapHttp($target, $referer, $method = self::GET_METHOD, $data_array = "", self::HEADER_INCLUDED);
    }

    /**
     * Submits a form with the GET method and downloads the page
     * (without a http header) referenced by the form's ACTION variable
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @param   string  $data_array An array that defines the form variables
     * @return  array               $return_array['FILE']   = Contents of fetched file,
     *                              $return_array['STATUS'] = CURL generated status of transfer,
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function getHttpForm($target, $referer, $data_array) 
    {
        return self::scrapHttp($target, $referer, $method = self::GET_METHOD, $data_array, self::HEADER_EXCLUDED);
    }
    
    /**
     * Submits a form with the GET method and downloads the page
     * (with http header) referenced by the form's ACTION variable
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @param   string  $data_array An array that defines the form variables
     * @return  array               $return_array['FILE']   = Contents of fetched file, will also
     *                              include the HTTP header if requested
     *                              $return_array['STATUS'] = CURL generated status of transfer
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function getHttpFormWithHeader($target, $referer, $data_array)
    {
        return self::scrapHttp($target, $referer, $method = self::GET_METHOD, $data_array, self::HEADER_INCLUDED);
    }
   
    /**
     * Submits a form with the POST method and downloads the page      
     * (without http header) referenced by the form's ACTION variable
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @param   string  $data_array An array that defines the form variables
     * @return  array               $return_array['FILE']   = Contents of fetched file, will also
     *                              include the HTTP header if requested
     *                              $return_array['STATUS'] = CURL generated status of transfer,
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function postHttpForm($target, $referer, $data_array) 
    {
        return self::scrapHttp($target, $referer, $method = self::POST_METHOD, $data_array, self::HEADER_EXCLUDED);
    }
    
    /**
     * Submits a form with the POST method and downloads the page      
     * (with http header) referenced by the form's ACTION variable
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @param   string  $data_array An array that defines the form variables
     * @return  array               $return_array['FILE']   = Contents of fetched file,
     *                              $return_array['STATUS'] = CURL generated status of transfer,
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function postHttpFormWithHeader($target, $referer, $data_array) 
    {
        return self::scrapHttp($target, $referer, $method = self::POST_METHOD, $data_array, self::HEADER_INCLUDED);
    }
    
    /**
     * Submits a form with the Head method and downloads the page      
     * header referenced by the form's ACTION variable
     * 
     * @param   string  $target     The target file (to download).
     * @param   string  $referer    The server referer variable.
     * @return  array               $return_array['STATUS'] = CURL generated status of transfer, will also
     *                              include the HTTP header if requested
     *                              $return_array['ERROR']  = CURL generated error status.
     */
    public static function getHttpHeader($target, $referer) 
    {
        return self::scrapHttp($target, $referer, $method = self::HEAD_METHOD, $data_array = "", self::HEADER_INCLUDED);
    }

       
    /**
     * This function returns a web page (HTML only) for a web page through
     * the execution of a simple HTTP GET request.
     * All HTTP redirects are automatically followed.
     * 
     * @param   string  $target         The target file (to download).
     * @param   string  $referer        The server referer variable.
     * @param   string  $method         Defines request HTTP method; HEAD, GET or POST
     * @param   string  $data_array     A keyed array, containing query string
     * @param type $include_header
     * @return type 
     */
    public static function scrapHttp($target, $referer, $method, $data_array, $include_header) 
    {
        $config = ConfigManager::bind();
        
        # Initialize PHP/CURL handle
        $ch = curl_init();

        # Prcess data, if presented
        if (is_array($data_array))
        {
            # Convert data array into a query string (ie animal=dog&sport=baseball)
            foreach ($data_array as $key => $value) 
            {
                if (strlen(trim($value)) > 0)
                    $temp_string[] = $key . "=" . urlencode($value);
                else
                    $temp_string[] = $key;
            }
            $query_string = join('&', $temp_string);
        }

        # HEAD method configuration
        if ($method == self::HEAD_METHOD) 
        {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);                // Http head
            curl_setopt($ch, CURLOPT_NOBODY, TRUE);                // No Return body
        } 
        else
        {
            # GET method configuration
            if ($method == self::GET_METHOD) 
            {
                if (isset($query_string))
                    $target = $target . "?" . $query_string;
                curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
                curl_setopt($ch, CURLOPT_POST, FALSE);
            }
            # POST method configuration
            if ($method == self::POST_METHOD)
            {
                if (isset($query_string))
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
            }
            curl_setopt($ch, CURLOPT_HEADER, $include_header);   // Include head as needed
            curl_setopt($ch, CURLOPT_NOBODY, FALSE);        // Return body
        }
        
        # Enable Proxy Use
        if ($config['http_scrap']['use_proxy'])
        {
            curl_setopt($ch, CURLOPT_PROXY, $config['http_scrap']['proxy']['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $config['http_scrap']['proxy']['port']);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $config['http_scrap']['proxy']['user'].":".$config['http_scrap']['proxy']['passwd']);            
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_ANYSAFE);            
            switch ($config['http_scrap']['proxy']['type']) 
            {
                case 'HTTP':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
                case 'SOCKS4':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                    break;
                case 'SOCKS5':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    break;
                default:
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
            }
        }

        curl_setopt($ch, CURLOPT_COOKIEJAR, $config['http_scrap']['cookie_file'] );                             // Cookie management.
        curl_setopt($ch, CURLOPT_COOKIEFILE, $config['http_scrap']['cookie_file'] );
        curl_setopt($ch, CURLOPT_TIMEOUT, $config['http_scrap']['curl_timeout']);                               // Timeout
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent = $config['http_scrap']['next_scrapping_agent']());     // Webbot name
        curl_setopt($ch, CURLOPT_URL, $target);                                                                 // Target site
        curl_setopt($ch, CURLOPT_REFERER, $referer);                                                            // Referer value
        curl_setopt($ch, CURLOPT_VERBOSE, $config['http_scrap']['verbose_scrapping']);                          // Minimize logs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);                                                        // No certificate
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $config['http_scrap']['accept_redirects']);                    // Follow redirects
        curl_setopt($ch, CURLOPT_MAXREDIRS, $config['http_scrap']['max_redirects']);                            // Limit redirections to four
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);                                                         // Return in string
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        # Create return array
        $return_array['FILE'] = curl_exec($ch);
        $return_array['STATUS'] = curl_getinfo($ch);
        $return_array['ERROR'] = curl_error($ch);
        $return_array['STATUS']['download_finished_time'] = new DateTime();

        # Close PHP/CURL handle
        curl_close($ch);
        # Log activity
        self::logActivity($target, $referer, $user_agent, $method, $return_array['STATUS']['http_code']);
        # Return results
        return $return_array;
    }
    
    private static function logActivity($url, $referer,$agent, $method, $http_code) 
    {
        EventDispatcher::bind()->notify(new Event(NULL, 'http_scraper.log', 
                array('url' => $url, 'referer' => $referer, 'agent' => $agent, 'method' => $method, 'http_code' => $http_code)));
    }
}