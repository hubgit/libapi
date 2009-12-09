<?php

function debug($arg){
  switch (Config::property('debug')){
    case 'PRINT':
      return print(print_r($arg, TRUE) . "\n");
    break;
    
    case 'OFF':
    break;
    
    default:
      error_log(print_r($arg, TRUE) . "\n", 3, Config::property('log'));    
    break;
  }
}

function snippet($text, $start, $end, $pad = 50){
  $length = mb_strlen($text);
  $position = array($start, $end);
  
  $start -= $pad;
  $start = max($start, 0);
  
  while ($start > 0 && preg_match('/\S/', mb_substr($text, $start, 1)))
    $start--;
    
  $end += $pad;
  $end = min($end, $length);
  while ($end < $length && preg_match('/\S/', mb_substr($text, $end, 1)))
    $end++;
    
  return mb_substr($text, $start, $position[0] - $start) . '{{{' . mb_substr($text, $position[0], $position[1] - $position[0]) . '}}}' . mb_substr($text, $position[1], $end - $position[1]);
}

function unsnippet($input){
  return str_replace(array('{{{', '}}}'), array('<b>', '</b>'), htmlspecialchars($input, NULL, 'UTF-8'));
}

function space_prefix_html_elements($html){
  return preg_replace("/<(p|div|br|h1|h2|h3|h4|h5|h6|ol|ul|li|pre|address|blockquote|dl|div|fieldset|form|hr|noscript|table|td|dd|dt)(\s|>)/", ' <$1$2', $html);
}
