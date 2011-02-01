<?php

class Atom {
  function __construct($title, $author, $params = array()){
    $this->dom = new DOMDocument('1.0', 'UTF-8');

    $this->feed = $this->dom->appendChild($this->dom->createElement('feed'));

    $this->addTextChild($this->feed, 'title', $title);
    
    if ($params['subtitle'])
      $this->addTextChild($this->feed, 'subtitle', $params['subtitle']);
    
    $id = $params['id'] ? $params['id'] : sprintf('http://%s%s', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
    $this->addTextChild($this->feed, 'id', $id);
    
    $updated = $params['updated'] ? $params['updated'] : date(DATE_ATOM);
    $this->addTextChild($this->feed, 'updated', $updated);

    $node = $this->feed->appendChild($this->dom->createElement('author'));
    $this->addTextChild($node, 'name', $author['name']);

    $this->addLinks($this->feed, $params['link']);
  }

  function addTextChild(&$parent, $name, $text){
    $node = $this->dom->createElement($name);
    $node->appendChild($this->dom->createTextNode($text));
    $parent->appendChild($node);
    return $node;
  }

  function addEntry($id, $title, $updated, $summary = NULL, $params = array()){
    $entry = $this->feed->appendChild($this->dom->createElement('entry'));

    $this->addTextChild($entry, 'id', $id);
    $this->addTextChild($entry, 'title', $title);
    $this->addTextChild($entry, 'updated', date(DATE_ATOM, $updated));
    
    if ($params['published'])
      $this->addTextChild($entry, 'published', date(DATE_ATOM, $params['published']));

    if (!is_null($summary))
      $this->addTextChild($entry, 'summary', $summary);

    $this->addLinks($entry, $params['link']);
    return $entry;
  }

  function addContent(&$parent, $type = 'xhtml', $params = array()){
    $content = $this->dom->createElement('content');
    $content->setAttribute('type', $type);
    $parent->appendChild($content);
    
    if ($type === 'xhtml'){
      $div = $this->dom->createElement('div');
      $div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
      $content->appendChild($div);
      return $div;
    }
    
    if ($params['base'])
      $content->setAttribute('xml:base', $params['base']);
   
    if ($params['lang'])
      $content->setAttribute('xml:lang', $params['lang']); 
    
    return $content;
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
