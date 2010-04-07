<?php

class Spotify extends API {
  public $doc = 'http://developer.spotify.com/en/metadata-api/overview/';
  public $server = 'http://ws.spotify.com';

  function track($q, $full = TRUE){
    $this->opensearch($this->server . '/search/1/track.xml', array('q' => $q));  
    $this->xpath->registerNamespace('s', 'http://www.spotify.com/ns/music/1');
    
    $nodes = $this->xpath->query("s:track");
    if (!$nodes->length)
      return FALSE;
    
    $node = $nodes->item(0);
    $uri = $node->getAttribute('href');
    
    if ($full){
      $this->lookup($uri);
      $node = $this->data;
    }
    
    $this->results[] = array(
      'href' => $uri,
      'artist' => $this->xpath->query("s:artist/s:name", $node)->item(0)->textContent,
      'album' => $this->xpath->query("s:album/s:name", $node)->item(0)->textContent,
      'track' => $this->xpath->query("s:name", $node)->item(0)->textContent,
      );
  }

  function album($q, $full = TRUE){
    $this->opensearch($this->server . '/search/1/album.xml', array('q' => $q));
    $this->xpath->registerNamespace('s', 'http://www.spotify.com/ns/music/1');
       
    $nodes = $this->xpath->query("s:album");
    if (!$nodes->length)
      return FALSE;
    
    $node = $nodes->item(0);
    $uri = $node->getAttribute('href');
    
    if ($full){
      $this->lookup($uri, array('extras' => 'track')); // 'trackdetail'
      $node = $this->data;
    }
          
    $tracks = array();
    foreach ($this->xpath->query("s:tracks/s:track", $node) as $item)
      $tracks[] = $node->getAttribute('href');

    $this->results[] = array(
      'href' => $uri,
      'artist' => $this->xpath->query("s:artist/s:name", $node)->item(0)->textContent,
      'album' => $this->xpath->query("s:name", $node)->item(0)->textContent,
      'released' => $this->xpath->query("s:released", $node)->item(0)->textContent,
      'tracks' => $tracks,
      );
  }

  function lookup($uri, $params = array()){
    $default = array('uri' => $uri);
    $this->get_data($this->server . '/lookup/1/', array_merge($default, $params), 'dom');
    $this->xpath->registerNamespace('s', 'http://www.spotify.com/ns/music/1');
  }
}
