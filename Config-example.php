<?php

class Config {
  static $properties = array(
    'TIMEZONE' => 'Europe/London',
    'DATA_DIR' => '/tmp/libapi-data',
    'DEBUG' => 'PRINT', // OFF, FILE, PRINT
    'LOG' => '',
    
    /* DATABASE */
    'DB' => 'libapi',
    'DB_USER' => 'libapi',
    'DB_PASS' => '',
    
    /* API keys */  
    //'GOOGLE_MAPS' => '', 
    //'YAHOO' => '',
    //'FLICKR' => '',
    //'FLICKR_SECRET' => '',
    );
    
    /* DO NOT EDIT BELOW HERE */
    
    static function get($key, $default = null){
      if (isset(Config::$properties[$key]) && !empty(Config::$properties[$key]))
        return Config::$properties[$key];
      else if (!is_null($default))
        return $default;
      else
        throw new Exception('Config value not defined: ' . $key);
     }

    static function set($key, $value){
      Config::$properties[$key] = $value; 
    }
}