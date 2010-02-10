<?php

define(CSV_SEPARATOR, "\t");

// singleton
class CSV {
  static $input;
  static $output;
  static $separator = "\t";
  
  static function open($file, $state = 'r'){
    switch ($state){
      case 'w':
      case 'a': // append
        if (CSV::$output)
          fclose(CSV::$output);
        CSV::$output = fopen($file, $state);
      break;
      
      case 'r':
      default:
        if (CSV::$input)
          fclose(CSV::$input);
        CSV::$input = fopen($file, 'r');
      break;
    }  
  }
  
  static function skip($rows){
    foreach(range(1, $rows) as $i)
      CSV::read_line();
  }
  
  static function read_line($separator = CSV_SEPARATOR){
    return fgetcsv(CSV::$input, NULL, $separator);
  }
  
  static function read($separator = CSV_SEPARATOR){
    $items = array();
    while (($data = CSV::read_line($separator)) !== FALSE)
      $items[] = $data;
    return $items;
  }
  
  static function write($data, $separator = CSV_SEPARATOR){
    debug($data);
    if (!empty($data))
      fputcsv(CSV::$output, $data, $separator);
  }
}
