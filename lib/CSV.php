<?php

// singleton
class CSV {
  static $input;
  static $output;
  
  static function open($file, $state = 'r'){
    switch ($state){
      case 'w':
        if (CSV::$output)
          fclose(CSV::$output);
        CSV::$output = fopen($file, 'w');
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
  
  static function read_line($separator = ','){
    return fgetcsv(CSV::$input, NULL, $separator);
  }
  
  static function read($separator = ','){
    $items = array();
    while (($data = CSV::read_line($separator)) !== FALSE)
      $items[] = $data;
    return $items;
  }
  
  static function write($data, $separator = ','){
    fputcsv(CSV::$output, $data, $separator);
  }
}
