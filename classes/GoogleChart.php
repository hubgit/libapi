<?php

class GoogleChart extends API {
  public $doc = 'http://code.google.com/apis/chart/docs/';
  public $cache = TRUE;
  private $server = 'http://chart.apis.google.com/chart';
  
  function chart($params = array()){     
    $data = array(
      'cht' => $params['type'],
      'chs' => $params['size'],
      'chd' => $params['datatype'] . ':' . implode(',', $params['data']),
      'chxt' => implode(',', array_keys($params['axes'])),
      'chxs' => implode('&', array_values($params['axes'])),
    );
    
    $this->get_data($this->server, $data, 'raw');
  } 
}