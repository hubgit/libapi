<?php

class Spotify extends API {
  public $doc = 'http://developer.spotify.com/en/metadata-api/overview/';
  public $server = 'http://ws.spotify.com';
  
  function track($q){
    if (!$q)
      return FALSE;
    
    $cache_dir = $this->get_output_dir('spotify/cache/search/track');
    $cache_file = sprintf('%s/%s.xml', $cache_dir, md5($q));
  
    if (file_exists($cache_file) && ((filemtime($cache_file) - time()) < 60*60*24)){ // use the cache file if it's less than one day old
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
    
    return $xml->track;
  }
  
  function album($q){
    if (!$q)
      return FALSE;
    
    $tracks = $this->track($q);
    $track = $tracks[0];
    
    $album_href = (string) $track->album['href'];
    
    $albums = array();
    foreach ($xml->track as $track)
      if ((string) $track->album['href'] == $album_href)
        $albums[$album_href][(int) $track->{'track-number'}] = (string) $track['href'];
    
    $tracks = array();
    foreach ($albums as $id => $items){
      ksort($items);
      foreach ($items as $item)
        $tracks[] = $item;
    }
    
    $album = array(
      'href' => $album_href,
      'artist' => (string) $track->artist->name,
      'album' => (string) $track->album->name,
      'released' => (string) $track->album->released,
      'tracks' => $tracks, //implode(' ', $tracks),
      //'territories' => (string) $track->album->availability->territories,
      );    

    return $album;
  }
}
