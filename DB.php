<?php

class DB {
  public $host = 'localhost';
  
  function __construct($host = 'localhost'){    
    mysql_connect($host, Config::get('DB_USER'), Config::get('DB_PASS')) or die("Could not connect to MySQL database\n");
    mysql_select_db(Config::get('DB')) or die("Could not select database\n");

    mysql_query('SET CHARACTER SET utf8');
    //mysql_query('SET NAMES utf8');
  }
  
  function query(){
    $params = func_get_args();
    $query = array_shift($params);

    if (!empty($params)){
      if (is_array($params[0]))
        $params = $params[0];

      foreach ($params as $key => $value)
        if (!is_int($value))
          $params[$key] = mysql_real_escape_string($value);
    }

    $sql = vsprintf($query, $params);
    //debug($sql);

    $result = mysql_query($sql);
    if (mysql_errno())
      exit(sprintf("MySQL error %d:\n\t%s\n", mysql_errno(), mysql_error()));

    return $result;
  }
}



