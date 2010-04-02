<?php

class Atom {
  function __construct($title, $author, $params = array()){
    $this->dom = new DOMDocument('1.0', 'UTF-8');
    $this->feed = $this->dom->appendChild($this->dom->createElement('feed'));  
    
    $this->addTextChild($this->feed, 'title', $title);
    $this->addTextChild($this->feed, 'id', sprintf('http://%s%s', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']));
    $this->addTextChild($this->feed, 'updated', date(DATE_ATOM));
        
    $node = $this->feed->appendChild($this->dom->createElement('author'));
    $this->addTextChild($node, 'name', $author['name']);
        
    $this->addLinks($this->feed, $params['link']);
  }
  
  function addTextChild(&$parent, $name, $text){
    $node = $this->dom->createElement($name);
    $node->appendChild($this->dom->createTextNode($text));
    $parent->appendChild($node);
  }
  
  function addEntry($id, $title, $updated, $summary, $params){
    $entry = $this->feed->appendChild($this->dom->createElement('entry'));
    
    $this->addTextChild($entry, 'id', $id);
    $this->addTextChild($entry, 'title', $title);
    $this->addTextChild($entry, 'updated', date(DATE_ATOM, $updated));
    $this->addTextChild($entry, 'summary', $summary);
    
    $this->addLinks($entry, $params['link']);
  }
  
  function addLinks(&$parent, $links){
    if (empty($links))
      return FALSE;
      
    foreach ($links as $type => $url){
      $node = $parent->appendChild($this->dom->createElement('link'));
      $node->setAttribute('rel', 'alternate');
      $node->setAttribute('type', $type);
      $node->setAttribute('href', $url);
    }
  }
  
  function output(){
    $this->feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');

    header('Content-Type: application/atom+xml; charset=UTF-8');
    $this->dom->formatOutput = TRUE;
    print $this->dom->saveXML();
  }
}