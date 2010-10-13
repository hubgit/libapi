<?php

class Freebase extends API{
  public $doc = 'http://wiki.freebase.com/wiki/Reconciliation';


  function reconcile($args, $params = array()){
    unset($this->total, $this->data, $this->webenv, $this->querykey);

    $parts = $this->build_reconciliation_query($args);

    $default = array(
      'q' => json_encode($parts),
      'start' => 0,
      'limit' => 10,
      'jsonp' => null,
      );

    $this->get_data('http://data.labs.freebase.com/recon/query', array_merge($default, $params), 'json');

    if (!is_array($this->data))
      throw new HTTPException(500, 'Error searching Freebase Reconciliation Service');

    $this->total = count($this->data);
    return $this->data;
  }
}

