<?php

class DB {
  public $link;

  function __construct($host = 'localhost', $database = NULL, $user = NULL, $password = NULL){
    if (!isset($database))
      $database = Config::get('DB');

    if (!isset($user))
      $user = Config::get('DB_USER');

    if (!isset($password))
      $password = Config::get('DB_PASS');

    $this->link = mysql_connect($host, $user, $password) or die("Could not connect to MySQL database\n" . mysql_errno());
    mysql_select_db($database, $this->link) or die("Could not select database\n");

    mysql_query('SET CHARACTER SET utf8', $this->link);
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

    $result = mysql_query($sql, $this->link);
    if (mysql_errno($this->link))
      throw new Exception(sprintf("MySQL error %d:\n\t%s\n", mysql_errno($this->link), mysql_error($this->link)));

    return $result;
  }
}

