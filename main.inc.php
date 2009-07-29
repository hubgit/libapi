<?php

set_include_path(implode(PATH_SEPARATOR, array(
  dirname(__FILE__),
  dirname(__FILE__) . '/lib',
  get_include_path()
  )));

set_time_limit(0);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

libxml_use_internal_errors(FALSE); // true = hide parsing errors; use libxml_get_errors() to display later.

require 'common.inc.php';
include 'common.private.inc.php'; // private functions

require 'config.inc.php';

if (!defined('DATA_DIR'))
  define('DATA_DIR', dirname(__FILE__) . '/data');

class API {
  function __construct($action, $sources = NULL){
    if (!$action)
      exit('No action has been set');
      
    $this->action = $action;
    
    $available = array();
    
    $match = sprintf('%s/sources/%s/*.inc.php', dirname(__FILE__), $action);
    foreach (glob($match) as $file)
      if (include_once($file))
        $available[] = preg_replace('/\.private$/', '', basename($file, '.inc.php'));
    
    // use all available sources if none were specified
    if (!isset($sources)){
      $this->sources = $available;
    }
    else{
      // if specific sources were defined, use only those
      if (is_string($sources))
        $sources = array($sources);
    
      $this->sources = array();
      foreach ($sources as $source)
        if (in_array($source, $available))
          $this->sources[] = $source;
    }
    
    if (empty($this->sources))
      exit(sprintf('No sources are enabled for action "%s"', $action));
      
    debug($this->sources);
    return $this->sources;
  }
    
  function run($q){
    if (!$this->action)
      exit('No action has been set');
      
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
