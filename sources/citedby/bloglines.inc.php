<?php

# http://searchapidocs.scopus.com/

return (defined('BLOGLINES_KEY') && defined('BLOGLINES_USER'));

function citedby_bloglines($doi){
  $url = 'http://dx.doi.org/' . $doi;
  $url = 'http://hublog.hubmed.org/';

  $xml = get_data('http://www.bloglines.com/search', array(
    'format' => 'publicapi',
    'apiuser' => BLOGLINES_USER,
    'apikey' => BLOGLINES_KEY,
    's' => 'f',
    'q' => 'bcite:' . $url,
  ), 'xml');
  
  
  //debug($xml);
  
  if (!is_object($xml) || !$xml->resultset['found'])
    return array(FALSE, FALSE, array());
  
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

