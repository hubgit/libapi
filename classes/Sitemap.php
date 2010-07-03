<?php

class Sitemap extends API {  
  function scrape($url){
    $this->get_data($url, array(), 'dom');
    $this->xpath->registerNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    
    $items = array(); 
    foreach ($this->xpath->query('sm:url') as $node){
      $item = array();
      foreach ($node->childNodes as $childNode)
        $item[$childNode->localName] = $childNode->nodeValue;
      $items[] = $item;
    }
    
    return $items;
  } 
}