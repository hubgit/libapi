<?php

if (!defined('DB_USER') || !defined('DB_PASS') || !defined('DB'))
  return FALSE;

mysql_connect('localhost', DB_USER, DB_PASS) or die("Could not connect to MySQL database\n");
mysql_select_db(DB) or die("Could not select database\n");

mysql_query('SET CHARACTER SET utf8');

function db_query(){
  $params = func_get_args();
  $query = array_shift($params);
  
  if (is_array($params[0]))
    $params = $params[0];
 
  foreach ($params as $key => $value)
    if (!is_int($value))
      $params[$key] = mysql_real_escape_string($value);
  
  $sql = vsprintf($query, $params);
  
  $result = mysql_query($sql);
  if (mysql_errno())
    exit(sprintf("MySQL error %d:\n\t%s\n", mysql_errno(), mysql_error()));
    
  return $result;
}


