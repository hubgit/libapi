<?php

require dirname(__FILE__) . '/common.inc.php';
include dirname(__FILE__) . '/common.private.inc.php'; // private functions

require dirname(__FILE__) . '/config.inc.php';
require dirname(__FILE__) . '/disabled.inc.php';

class API {
  function __construct($action, $sources = array()){
    if (!$action)
      exit('No action has been set');
      
    $this->action = $action;
    
    if (is_string($sources))
      $sources = array($sources);
      
    if (!empty($sources))
      return $this->sources = $sources;
    
    $this->sources = array();

    global $disabled;
    
    $match = sprintf('%s/sources/%s/*.inc.php', dirname(__FILE__), $action);
    foreach (glob($match) as $file){ 
      $source = basename($file, '.inc.php');
      if (!in_array($source, $disabled[$action]) && include_once($file))
        $this->sources[] = $source;
    }
    
    if (empty($this->sources))
      exit(sprintf('No sources are enabled for action "%s"', $action));
      
    debug($this->sources);
    return $this->sources;
  }
    
  function all($q){
    if (empty($this->sources))
      exit(sprintf('No sources are active for action "%s"', $this->action));
    
    $responses = array();
    foreach ($this->sources as $source){
      $function = sprintf('%s_%s', $this->action, $source);
      debug($function . '...');
      $responses[$source] = call_user_func($function, $q);
    }
    return $responses;
  }
}
