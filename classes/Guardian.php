<?php

class Guardian extends API {
  public $doc = 'http://api.guardianapis.com/docs';
  public $def = 'GUARDIAN';
    
  private $daily_limit = 5000;
  
  public $n = 50;

  function content($filter){
    $page = 0; // results start at 0
        
    do{
      $start = $page * $this->n;      
      $this->get_data('http://api.guardianapis.com/content/search', array(
        'api_key' => Config::get('GUARDIAN'),
        'content-type' => 'article',
        'filter' => $filter,
        'format' => 'json',
        'count' => $this->n,
        'start-index' => $start,
      ));
    
      if (empty($this->data->search->results))
        break;
      
      foreach ($this->data->search->results as $item){
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%d.js', $this->output_dir, $this->base64_encode_file($item->id)), json_encode($item)); 
        else
          $this->results[] = $item;
      }

      sleep(1);
    } while ($start < $this->data->search->count && ++$page < $this->daily_limit);
  }
  
  function search($q, $params = array()){
    $default = array(
      'api_key' => Config::get('GUARDIAN'),
      'content-type' => 'article',
      'format' => 'json',
      'q' => $q,
    );

    $this->get_data('http://api.guardianapis.com/content/search', array_merge($default, $params));

    $this->results = $this->data->search->results;
    $this->total = $this->data->search->count;
  }
}