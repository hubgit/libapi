<?php

function debug($arg){
  switch (Config::get('DEBUG')){
    case 'PRINT':
      return print(print_r($arg, TRUE) . "\n");
    break;
    
    case 'OFF':
    break;
    
    default:
      error_log(print_r($arg, TRUE) . "\n", 3, Config::get('LOG'));    
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

function parse_http_headers($headers){
  $items = array();
  $item = array();
  $status = 0;
  
  foreach ($headers as $header){
    if (preg_match('/HTTP\/.+?\s+(\d+)\s+(.+)/', $header, $matches)){
      if ($status){
        // convert arrays to strings if only one item
        foreach ($item as &$data)
          if (count($data) === 1)
            $data = $data[0];
  
        $items[$status][] = $item;
      }
      
      $status = $matches[1];
      $item = array();
      continue;
    }
    
    preg_match('/(.+?):\s+(.+)/', $header, $matches);
    $item[strtolower($matches[1])][] = $matches[2];
  }
  
  // convert arrays to strings if only one item
  foreach ($item as &$data)
    if (count($data) === 1)
      $data = $data[0];
  $items[$status][] = $item;

  return $items;
}

function parse_accept_header($params = array()){
  $formats = array_merge(array(
    'text/html' => 'html',
    'text/plain' => 'text',
    'application/xhtml+xml' => 'xhtml',
    'application/xml' => 'xml',
    'application/json' => 'json',
    'application/pdf' => 'pdf',
    '*/*' => 'html',
    ), $params);
    
  // parse the HTTP Accept header
  $accept = array();
  foreach (preg_split('/\s*,\s*/', $_SERVER['HTTP_ACCEPT'], NULL, PREG_SPLIT_NO_EMPTY) as $header){
    list($mime, $q) = preg_split('/\s*;\s*q\s*=\s*/', $header);
    $accept[strtolower($mime)] = ($q === null)? 1 : $q;
  }
  
  if (empty($accept))
    return array('*/*', $formats['*/*']);
  
  // sort the accepted formats in descending order of preference
  arsort($accept);

  // pick the format with the highest value
  foreach ($accept as $mime => $q)
    if ($formats[$mime])
      break;

  return $formats[$mime];
}

function parse_file_extension($params = array()){
  $extensions = array_merge(array(
    '.json' => 'json',
    '.html' => 'html',
    '.htm' => 'html',
    '.xml' => 'xml',
    '.js' => 'json',
    '.txt' => 'text',
    '.ris' => 'ris',
    '.bibtext' => 'bibtex',
    '.pdf' => 'pdf',
    ), $params);
  
  
  $path = pathinfo($_SERVER['PATH_INFO']); // $_SERVER['REQUEST_URI']?
  if (!$path['extension'])
    return false;
    
  $extension = strtolower($path['extension']);
  if (isset($extensions[$extension]))
    return $extensions[$extension];
}

function send_content_type_header($format, $params = array(), $charset = 'utf-8'){
  $types = array_merge(array(
    'html' => 'text/html',
    'text' => 'text/plain',
    'xml' => 'application/xml',
    'json' => 'application/json',
    'pdf' => 'application/pdf',
    'ris' => 'application/ris',
    'bibtext' => 'application/bibtex',
  ), $params);
  
  header(sprintf('Content-type: %s; charset="%s"', $types[$format], $charset));
}
