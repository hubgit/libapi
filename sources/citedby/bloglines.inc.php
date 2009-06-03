<?php

# http://searchapidocs.scopus.com/

return (defined('BLOGLINES_KEY') && defined('BLOGLINES_USER'));

function citedby_bloglines($q){
  if (!$q['url'] && $q['doi']))
    $q['url'] = 'http://dx.doi.org/' . $q['doi'];
    
  if (!$url = $q['url'])
    return FALSE;

  $xml = get_data('http://www.bloglines.com/search', array(
    'format' => 'publicapi',
    'apiuser' => BLOGLINES_USER,
    'apikey' => BLOGLINES_KEY,
    's' => 'f',
    'q' => 'bcite:' . $q['url'],
  ), 'xml');
  
  //debug($xml);
  
  if (!is_object($xml) || !$xml->resultset['found'])
    return FALSE;
  
  $items = array();
  foreach ($xml->xpath("resultset[@qtype='article']/result") as $item){
    $items[] = array(
      'url' => (string) $item->url,
      'title' => (string) $item->title,
      'author' => (string) $item->author,
      'description' => (string) $item->abstract,
      'date' => strtotime((string) $item['date']),
      'site' => array(
        'name' => (string) $item->site->name,
        'url' => (string) $item->site->url,
        'subs' => (int) $item->site['nsubs'],
        ),
     );
  }
    
  return array((int) $xml->resultset['found'], str_replace('format=rss', '', (string) $xml->link['href']), $items);
}

