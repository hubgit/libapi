<?php

class GoogleCalendar extends API {
  function content($id, $params = array()){
    if (!$id)
      return FALSE;
      
    $now = time();
    $week = 3600 * 24 * 7;
  
    $default = array(
      'start-min' => $now - $week, // default 1 week ago
      'start-max' => $now + $week, // default 1 week in the future
      'orderby' => 'starttime',
      'sortorder' => 'ascending',
      'singleevents' => 'true',
      'ctz' => 'Europe/London', // FIXME: default timezone
      'max-results' => 10,
      );
    
    $params = array_merge($default, $params);
    
    foreach (array('min', 'max') as $item)
      if (is_integer($params['start-' . $item]))
        $params['start-' . $item] = date(DATE_ATOM, $params['start-' . $item]); // '20100101T12:00:00'
  
    $url = sprintf('http://www.google.com/calendar/feeds/%s/public/full', urlencode($id));
    $dom = $this->get_data($url, $params, 'dom');
    //$dom->formatOutput = TRUE;
    //debug($dom->saveXML());
    
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
    $xpath->registerNamespace('gd', 'http://schemas.google.com/g/2005');

    $items = array();
    foreach ($xpath->query('atom:entry') as $entry){
      $when = $xpath->query('gd:when', $entry)->item(0);
    
      $items[] = array(
        'title' => $xpath->query("atom:title[@type='text']", $entry)->item(0)->textContent,
        'content' => $xpath->query("atom:content[@type='text']", $entry)->item(0)->textContent,
        'start' => strtotime($when->getAttribute('startTime')),
        'end' => strtotime($when->getAttribute('endTime')),
      );
    }
    
    return $items;
  }
}
