<?php

class Libapi_Config {
  static $properties = array(
    'TIMEZONE' => 'Europe/London',
    'DATA' => '/path/to/libapi-data',
    'DEBUG' => 'PRINT', // OFF, FILE, PRINT
    'LOG' => '',
    
    /* DATABASE */
    'DB' => 'libapi',
    'DB_USER' => 'libapi',
    'DB_PASS' => '',
    
    /* API keys */  
    'GOOGLE_MAPS' => '', 
    'YAHOO' => '',
    'FLICKR' => '',
    'FLICKR_SECRET' => '',
    );
    
    /* DO NOT EDIT BELOW HERE */
    
    static function get($key){
      if (isset(Libapi_Config::$properties[$key]) && !empty(Libapi_Config::$properties[$key]))
        return Libapi_Config::$properties[$key];
      else
        throw new Exception('Libapi_Config value not defined: ' . $key);
     }

    static function set($key, $value){
      Libapi_Config::$properties[$key] = $value; 
    }
}