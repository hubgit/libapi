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

function __autoload($class){
   require_once(sprintf('%s/classes/%s.php', ROOT_DIR, $class));
}

require 'common.inc.php';
include 'common.private.inc.php'; // private functions

require 'config.inc.php';

if (!defined('DATA_DIR'))
  define('DATA_DIR', ROOT_DIR . '/data');

if (!defined('DEBUG_LOG'))
  define('DEBUG_LOG', DATA_DIR . '/debug.log');
  
define('MISC_DIR', ROOT_DIR . '/misc');
  
