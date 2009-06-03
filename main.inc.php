<?php

require dirname(__FILE__) . '/common.inc.php';
require dirname(__FILE__) . '/config.inc.php';
require dirname(__FILE__) . '/enabled.inc.php';

class API {
  function __construct($action){
    $this->action = $action;
    
    global $enabled;
    if (empty($enabled[$action]))
      exit(sprintf('No sources are enabled for action "%s"', $action));
    
    $this->sources = array();
    $match = sprintf('%s/sources/%s/*.inc.php', dirname(__FILE__), $action);
    foreach (glob($match) as $file){ 
      $source = basename($file, '.inc.php');
      if (in_array($source, $enabled[$action]) && include_once($file))
        $this->sources[] = $source;
    }
    debug($this->sources);
  }
    
  function all($q){
    if (empty($this->sources))
      exit(sprintf('No sources are active for action "%s"', $action));
    
    $responses = array();
    foreach ($this->sources as $source){
      $function = sprintf('%s_%s', $this->action, $source);
      debug($function . '...');
      $responses[$source] = call_user_func($function, $q);
    }
    return $responses;
  }
}
