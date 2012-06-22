<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../lib/autoloader/autoloader.php';

// capturing script params
// eg: php launcher.php RevolicoAdsBot 2 http://jronda63.loginto.me/servicios/
// params:  0: file; 1: webbot; 2: instance_name; 3: target url; 4:last_scrap
$params = $argv;  
array_shift($params);
$bot_class = array_shift($params);

$context = Context::createInstance($bot_class::createInstance($params));
$context->execute();
