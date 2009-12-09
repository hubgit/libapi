<?php

define('LIBAPI_ROOT', dirname(__FILE__));

set_include_path(implode(PATH_SEPARATOR, array(
  LIBAPI_ROOT,
  LIBAPI_ROOT . '/lib',
  get_include_path()
  )));

set_time_limit(0);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

libxml_use_internal_errors(FALSE); // true = hide parsing errors; use libxml_get_errors() to display later.

require 'functions.php';
require 'Config.php';

date_default_timezone_set(Config::get('TIMEZONE'));

if (empty(Config::$properties['DATA']))
  Config::set('DATA', LIBAPI_ROOT . '/data');
define('DATA_DIR', Config::get('DATA')); // shortcut = DATA_DIR

if (empty(Config::$properties['LOG']))
  Config::set('LOG', DATA_DIR . '/debug.log');

define('MISC_DIR', LIBAPI_ROOT . '/misc');

require LIBAPI_ROOT . '/API.php';
require LIBAPI_ROOT . '/DB.php';

spl_autoload_register(array('API', '__autoload'));

  
