<?php

class Output {
  static function raw($input){
    print $input;
  }
  
  static function html($input, $type = NULL){
    if (is_object($input))
      debug($input);
    
    switch($type){
      case 'url':
      $input = urlencode($input);
      break;
      
      default:
      break; 
    }
    
    $input = mb_convert_encoding($input, 'UTF-8', mb_detect_encoding($input));
    print htmlspecialchars((string) $input, ENT_QUOTES, 'UTF-8'); // FIXME: filter_var + FILTER_SANITIZE_SPECIAL_CHARS?
  }
}

