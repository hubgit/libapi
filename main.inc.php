<?php

require dirname(__FILE__) . '/config.inc.php';
require dirname(__FILE__) . '/common.inc.php';

class API {
  function __construct($q){
    global $enabled;
    $this->q = $q;
    
    $this->actions = array();
    foreach (glob(dirname(__FILE__) . '/sources/*', GLOB_ONLYDIR) as $dir){
      $action = end(explode('/', $dir));
          
      foreach (glob("$dir/*.inc.php") as $file){
        $source = basename($file, '.inc.php');
        if (in_array($source, $enabled[$action]) && include_once($file))
          $this->actions[$action][] = $source;
      }
    }
  }
    
  function all($action){
    if (!isset($this->actions[$action]))
      exit(sprintf('no active sources for "%s"', $action));
    
    $responses = array();
    foreach ($this->actions[$action] as $source){
      $function = sprintf('%s_%s', $action, $source);
      debug($function . '...');
      $responses[$source] = call_user_func($function, $this->q);
    }
    return $responses;
  }
}
