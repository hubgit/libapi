<?php

class Police extends API {
  public $doc = 'http://policeapi.rkh.co.uk/';

  public $def = 'POLICE';

  public $server = 'http://policeapi.rkh.co.uk/api/';

  public $cache = TRUE;

  function get_data($path, $params = array()){
    parent::get_data($this->server . $path, $params + array('key' => Config::get('POLICE')), 'dom');
  }

  function forces(){
    $this->get_data('forces');
  }

  function areas($force){
    $this->get_data('crime-areas', array('force' => $force));
  }
}

