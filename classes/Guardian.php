<?php

class Guardian extends API {
  public $doc = 'http://api.guardianapis.com/docs';
  public $def = 'GUARDIAN';

  function content($args){
    $this->validate($args, 'guardian_filter'); extract($args);
    
    if ($output)
      $this->output_dir = $this->get_output_dir($output);
  
    $n = 50;
    $page = 0; // results start at 0
  
    $daily_limit = 5000;
  
    $items = array();
  
    do{
      $start = $page * $n;
      //print "$start\n";
      
      $json = $this->get_data('http://api.guardianapis.com/content/search', array(
        'api_key' => Config::get('GUARDIAN'),
        'content-type' => 'article',
        'filter' => $guardian_filter,
        'format' => 'json',
        'count' => $n,
        'start-index' => $start,
      ));
    
      if (!is_object($json) || empty($json->search->results))
        break;
      
      foreach ($json->search->results as $item){
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%d.js', $this->output_dir, $this->base64_encode_file($item->id)), json_encode($item)); 
        else
          $items[] = $item;
      }

      sleep(1);
    } while ($start < $json->search->count && ++$page < $daily_limit);
  
    return $items;
  }
  
  function search($q, $params = array()){
    if (!$q)
      return FALSE;

    $default = array(
      'api_key' => Config::get('GUARDIAN'),
      'content-type' => 'article',
      'format' => 'json',
      'q' => $q,
    );

    $json = $this->get_data('http://api.guardianapis.com/content/search', array_merge($default, $params));

    if (!is_object($json))
      return FALSE;

    return array($json->search->results, array('total' => $json->search->count));
  }
}