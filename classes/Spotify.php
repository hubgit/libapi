<?php

class Spotify extends API {
  public $doc = 'http://developer.spotify.com/en/metadata-api/overview/';
  public $server = 'http://ws.spotify.com';

  function get_data($uri, $params = array()){
    $suffix = empty($params) ? NULL : '?' . http_build_query($params);

    $cache_dir = $this->get_output_dir('spotify/cache');
    $cache_file = sprintf('%s/%s.xml', $cache_dir, md5($uri . $suffix));

    if (file_exists($cache_file) && ((filemtime($cache_file) - time()) < 60*60*24)) // use the cache file if it's less than one day old
      $xml = simplexml_load_file($cache_file);
    else
      if (is_object($xml = parent::get_data($uri, $params, 'xml')))
        file_put_contents($cache_file, $xml->asXML());

    debug($xml);
    return $xml;
  }

  function track($q){
    if (!$q)
      return FALSE;

    $xml = $this->get_data($this->server . '/search/1/track.xml', array('q' => $q));
    if (!$items = $xml->track)
      return FALSE;
      
    if (!is_array($items))
      $items = array($items);
      
    $uri = (string) $items[0]['href'];
    $item = $this->lookup(array('uri' => $uri));
        
    return array(
      'href' => $uri,
      'artist' => (string) $item->artist->name,
      'album' => (string) $item->album->name,
      'track' => (string) $item->name,
      );
  }

  function album($q){
    if (!$q)
      return FALSE;

    $xml = $this->get_data($this->server . '/search/1/album.xml', array('q' => $q));
    if (!$items = $xml->album)
      return FALSE;

    if (!is_array($items))
      $items = array($items);

    $uri = (string) $items[0]['href'];
    $item = $this->lookup(array('uri' => $uri, 'extras' => 'track')); // 'trackdetails'

    $tracks = array();
    if (!empty($item->tracks->track))
      foreach ($item->tracks->track as $track)
        $tracks[] = (string) $track['href'];

    return array(
      'href' => $uri,
      'artist' => (string) $item->artist->name,
      'album' => (string) $item->name,
      'released' => (string) $item->released,
      'tracks' => $tracks,
      'raw' => $xml,
      //'territories' => (string) $items[0]->availability->territories,
      );
  }

  function lookup($args = array()){
    $this->validate($args, 'uri'); extract($args);

    return $this->get_data($this->server . '/lookup/1/', array('uri' => $uri));
  }
}
