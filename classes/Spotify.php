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
    foreach ($item->tracks->track as $track)
      $tracks[] = (string) $track['href'];

    return array(
      'href' => $uri,
      'artist' => (string) $item->artist->name,
      'album' => (string) $item->name,
      'released' => (string) $item->released,
      'tracks' => $tracks,
      //'territories' => (string) $items[0]->availability->territories,
      );
  }

  function lookup($q = array()){
    if (!$q['uri'])
      return FALSE;

    return $this->get_data($this->server . '/lookup/1/', $q);
  }

  /*
  function album_old($q){
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
  */
}
