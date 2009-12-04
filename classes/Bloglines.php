<?php

class Bloglines extends API {
  public $doc = 'http://www.bloglines.com/search';
  public $def = array('BLOGLINES', 'BLOGLINES_USER'); 
  
  function citedby($q){
    if (!$q['url'] && $q['doi']))
      $q['url'] = 'http://dx.doi.org/' . $q['doi'];

    if (!$url = $q['url'])
      return FALSE;

    $xml = $this->get_data('http://www.bloglines.com/search', array(
      'format' => 'publicapi',
      'apiuser' => Config::get('BLOGLINES_USER'),
      'apikey' => Config::get('BLOGLINES'),
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
    
    $meta = array(
      'total' => (int) $xml->resultset['found'],
      'url' => str_replace('format=rss', '', (string) $xml->link['href']),
      );

    return array($items, $meta);
  }
}