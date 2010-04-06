<?php

class Bloglines extends API {
  public $doc = 'http://www.bloglines.com/search';
  public $def = array('BLOGLINES', 'BLOGLINES_USER'); 
  
  public $results = array();
  public $total;
  public $link;
  
  function citedby($uri, $data = array()){
    if (!$uri && $data['doi'])
      $uri = 'http://dx.doi.org/' . $data['doi'];

    $this->get_data('http://www.bloglines.com/search', array(
      'format' => 'publicapi',
      'apiuser' => Config::get('BLOGLINES_USER'),
      'apikey' => Config::get('BLOGLINES'),
      's' => 'f',
      'q' => 'bcite:' . $uri,
    ), 'xml');
        
    if (!$this->data->resultset['found'])
      throw new DataException('Found no results');

    foreach ($this->data->xpath("resultset[@qtype='article']/result") as $item)
      $this->results[] = array(
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
    
    $this->total = (int) $xml->resultset['found'];
    $this->link = str_replace('format=rss', '', (string) $xml->link['href']);
  }
}