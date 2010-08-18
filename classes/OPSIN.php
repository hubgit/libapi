<?php

class OPSIN extends API {
    public $doc = 'http://opsin.ch.cam.ac.uk/instructions.html';
    public $cache = TRUE;
    
    function url($name, $format){
      return sprintf('http://opsin.ch.cam.ac.uk/opsin/%s.%s', urlencode($name), urlencode($format));
    }
    
    function get($name, $format){
      $url = $this->url($name, $format);
      return $this->get_data($url, array(), 'raw');
    }
}