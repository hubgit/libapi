<?php

class Police extends API {
  public $doc = 'http://policeapi.rkh.co.uk/';

  public $def = 'POLICE';

  public $server = 'http://policeapi.rkh.co.uk/api/';

  public $cache = TRUE;

  function get_data($path, $params = array()){
    return parent::get_data($this->server . $path, $params + array('key' => Config::get('POLICE')), 'dom');
  }

  function forces(){
    return $this->get_data('forces');
  }

  function types(){
    return $this->get_data('crime-types');
  }

  function areas($force){
    return $this->get_data('crime-areas', array('force' => $force));
  }

  function area($force, $area, $crimetype){
    return $this->get_data('crime-area', array('force' => $force, 'area' => $area, 'crimetype' => $crimetype));
  }
}

