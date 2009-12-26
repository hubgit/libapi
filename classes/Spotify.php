<?php

class Spotify extends API {
  public $doc = 'http://developer.spotify.com/en/metadata-api/overview/';
  public $server = 'http://ws.spotify.com';
  
  function album($q, $params = array()){
    if (!$q)
      return FALSE;
    
    $cache_dir = $this->get_output_dir('spotify/cache/search/track');
    $cache_file = sprintf('%s/%s.xml', $cache_dir, md5($q));
    
    if (file_exists($cache_file)){
      $xml = simplexml_load_file($cache_file); 
    }
    else {
      $xml = $this->get_data($this->server . '/search/1/track.xml', array('q' => $q), 'xml');

      if (!is_object($xml))
        return FALSE;
      
      file_put_contents($cache_file, $xml->asXML());
    }
      
    //debug($xml);
    
    if (!isset($xml->track))
      return FALSE;
    
    $track = $xml->track[0];
    $album = array(
      'href' => (string) $track->album['href'],
      'artist' => (string) $track->artist->name,
      'album' => (string) $track->album->name,
      'released' => (string) $track->album->released,
      //'territories' => (string) $track->album->availability->territories,
      );    

    return $album;
  }
}