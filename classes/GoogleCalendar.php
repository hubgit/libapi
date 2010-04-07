<?php

class GoogleCalendar extends API {  
  function content($id, $params = array()){
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
    $this->get_data($url, $params, 'dom');
    
    $this->xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
    $this->xpath->registerNamespace('gd', 'http://schemas.google.com/g/2005');

    foreach ($this->xpath->query('atom:entry') as $entry){
      $when = $this->xpath->query('gd:when', $entry)->item(0);
    
      $this->results[] = array(
        'title' => $this->xpath->query("atom:title[@type='text']", $entry)->item(0)->textContent,
        'content' => $this->xpath->query("atom:content[@type='text']", $entry)->item(0)->textContent,
        'start' => strtotime($when->getAttribute('startTime')),
        'end' => strtotime($when->getAttribute('endTime')),
      );
    }
  }
}
