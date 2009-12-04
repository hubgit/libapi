<?php

define('ROOT_DIR', dirname(__FILE__));

set_include_path(implode(PATH_SEPARATOR, array(
  ROOT_DIR,
  ROOT_DIR . '/lib',
  get_include_path()
  )));

set_time_limit(0);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

libxml_use_internal_errors(FALSE); // true = hide parsing errors; use libxml_get_errors() to display later.

require 'functions.php';
require 'Config.php';

date_default_timezone_set(Config::timezone);

function __autoload($class){
  $file = sprintf('%s/classes/%s', ROOT_DIR, $class);
  if (file_exists($file . '.private.php'))
    require_once($file . '.private.php');
  else if (file_exists($file . '.php'))
    require_once($file . '.php');
}

require ROOT_DIR . '/API.php';
require ROOT_DIR . '/DB.php';

if (empty(Config::data))
  Config::data = ROOT_DIR . '/data';
  
define('DATA_DIR', Config::data);

if (!empty(Config::log))
  Config::log = Config::data . '/debug.log';

define('MISC_DIR', ROOT_DIR . '/misc');
  
