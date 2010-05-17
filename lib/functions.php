<?php

function h($input, $print = TRUE){
  $input = mb_convert_encoding($input, 'UTF-8', mb_detect_encoding($input));
  $output = htmlspecialchars((string) $input, ENT_QUOTES, 'UTF-8'); // FIXME: filter_var + FILTER_SANITIZE_SPECIAL_CHARS?
  if ($print)
    print $output;
  else
    return $output;
}

function url($url, $params = array()){
  return $url . (empty($params) ? '' : '?' . http_build_query($params));
}

function debug($arg = ''){
  switch (Config::get('DEBUG')){
    case 'PRINT':
      if (is_string($arg)){
        $trace = debug_backtrace();
        $arg = sprintf('%s %s#%d:%s %s', microtime(), basename($trace[1]['file']), $trace[1]['line'], $trace[1]['function'], $arg);
      }
      print(print_r($arg, TRUE) . "\n");
    break;

    case 'OFF':
    break;

    case 'FIRE':
      $fire = FirePHP::getInstance(TRUE);
      if (is_string($arg)){
        $trace = debug_backtrace();
        $arg = sprintf('%s %.4f %s#%d:%s %s', date('H:i:s'), microtime(TRUE) - $_SERVER['REQUEST_TIME'], basename($trace[1]['file']), $trace[1]['line'], $trace[1]['function'], $arg);
      }
      $fire->log($arg);
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
  return str_replace(array('{{{', '}}}'), array('<span class="annotation">', '</span>'), htmlspecialchars($input, NULL, 'UTF-8'));
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

function innerXML($node){
  if (!is_object($node))
    return FALSE;

  if (get_class($node) == 'SimpleXMLElement')
    $node = dom_import_simplexml($node);


  if (get_class($node) == 'SimpleXMLElement')
    $node = dom_import_simplexml($node);

  $dom = new DOMDocument;
  if ($node->hasChildNodes())
    foreach ($node->childNodes as $child)
      $dom->appendChild($dom->importNode($child, TRUE));
  else
    return $node->textContent;

  return $dom->saveXML($dom->documentElement);
}

function outerXML($node){
  if (!is_object($node))
    return FALSE;

  if (get_class($node) == 'SimpleXMLElement')
    $node = dom_import_simplexml($node);

  $dom = new DOMDocument;
  $dom->appendChild($dom->importNode($node, TRUE));

  return $dom->saveXML($dom->documentElement);
}


function positions($haystack, $needle, $modifiers = 'u'){
  if (empty($needle))
    return array();

  $positions = array();

  $pattern = sprintf('/%s/%s', preg_quote($needle, '/'), $modifiers);
  preg_match_all($pattern, $haystack, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

  if (!empty($matches))
    foreach ($matches as $match)
      $positions[] = mb_strlen(mb_strcut($haystack, 0, $match[0][1])); // convert bytes to chars: PREG_OFFSET_CAPTURE returns byte offset, not chars, even with the 'u' modifier

  return $positions;
}

function truncate($string, $length, $suffix = ''){
  if (mb_strlen($string) <= $length)
    return $string;

  if ($suffix)
    $length -= mb_strlen($suffix) + 1;

  $string = mb_substr($string, 0, $length);

  if ($suffix)
    $string .= ' ' . $suffix;

  return $string;
}

function absolute_url($url, $base = NULL){
  /* return if already absolute URL */
  if (parse_url($url, PHP_URL_SCHEME) != '')
    return $url;

  $first = substr($url, 0, 1);

  /* anchors and queries */
  if ($first == '#' || $first == '?')
    return $base . $url;

  /* parse base URL and convert to local variables: $scheme, $host, $path */
  extract(parse_url($base));

  /* remove non-directory element from path */
  $path = preg_replace('#/[^/]*$#', '', $path);

  /* destroy path if relative url points to root */
  if ($first == '/')
    $path = '';

  /* dirty absolute URL */
  $url = $host . $path . '/' . $url;

  /* replace '//' or '/./' or '/foo/../' with '/' */
  for ($n = 1; $n > 0; $url = preg_replace(array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'), '/', $url, -1, $n)) {}

  /* absolute URL is ready! */
  return $scheme . '://' . $url;
}

function list_data($data, $show_value = TRUE){
  ?>
  <dl>
<? foreach ($data as $key => $value): ?>
    <div class="di">
      <dt><? h($key); ?></dt>
<? if (is_array($value)): ?>
      <dd><? h(implode(', ', $value)); ?></dd>
<? else: ?>
      <dd><? h($value); ?></dd>
<? endif; ?>
    </div>
<? endforeach; ?>
  </dl>
  <?
}

function mb_str_replace($needle, $replacement, $haystack){
  $needle_len = mb_strlen($needle);
  $replacement_len = mb_strlen($replacement);
  $pos = mb_strpos($haystack, $needle);
  while ($pos !== false){
    $haystack = mb_substr($haystack, 0, $pos) . $replacement . mb_substr($haystack, $pos + $needle_len);
    $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
  }
  return $haystack;
}

