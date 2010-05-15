<?php

set_time_limit(0);

//ini_set('soap.wsdl_cache_enabled', '0');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

libxml_use_internal_errors(FALSE); // true = hide parsing errors; use libxml_get_errors() to display later.

//ini_set('soap.wsdl_cache_enabled', '0');

define('LIBAPI_ROOT', dirname(__FILE__));
require LIBAPI_ROOT . '/lib/functions.php';
require LIBAPI_ROOT . '/Config.php';

date_default_timezone_set(Config::get('TIMEZONE'));

// start output buffering if not on command line
if (php_sapi_name() != 'cli' && !empty($_SERVER['REMOTE_ADDR'])){
  ob_start();
  require_once('FirePHPCore/FirePHP.class.php');
}

/* set up directories */
Config::set('MISC_DIR', LIBAPI_ROOT . '/misc');

if (empty(Config::$properties['DATA_DIR']))
  Config::set('DATA_DIR', LIBAPI_ROOT . '/data');

if (empty(Config::$properties['LOG']))
  Config::set('LOG', Config::get('DATA_DIR') . '/debug.log');

require LIBAPI_ROOT . '/API.php';

set_include_path(implode(PATH_SEPARATOR, array(
  LIBAPI_ROOT . '/classes-private/',
  LIBAPI_ROOT . '/classes/',
  LIBAPI_ROOT . '/lib/',
  LIBAPI_ROOT . '/extlib/',
  get_include_path())));
  
require 'DB.php';

function libapi_autoload($class){ require $class . '.php'; }
spl_autoload_register('libapi_autoload', FALSE, TRUE);
