<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../main.php';
Config::set('DEBUG', 'PRINT');

class SpotifyTest extends PHPUnit_Framework_TestCase {  
  public function setUp(){
    $this->api = new Spotify;
    $this->album_query = 'artist:"Nirvana" album:"Nevermind"';
    $this->track_query = 'artist:"Nirvana" album:"Nevermind" track:"Smells Like Teen Spirit"';
  }
  
  public function testAlbum(){
    $result = $this->api->album($this->album_query);
    $this->assertEquals('spotify:album:7fh6U3pYDTFZBjLaF2tyTp', $result['href']);
  }

  public function testTrack(){
    $result = $this->api->track($this->track_query);
    $this->assertEquals('spotify:track:2GHlIdLfbzWwsjzZPNfdrq', $result['href']);
  }
}
