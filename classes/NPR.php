<?php

class NPR extends API {
  public $doc = 'http://www.npr.org/api/'; # Note that full-text content is not available for some topics.
  public $def = 'NPR';

  function content($q){
    if (!$query = $q['npr-topic']) // eg 1007 = 'Health & Science' // http://www.npr.org/api/mappingCodes.php
      return FALSE;
    
    if (!is_numeric($query))
      return FALSE;
    
    if (isset($q['output']))
      $this->output_dir = $this->get_output_dir($q['output']);
  
    $n = 20;
    $page = 0; // results start at 0
    
    $items = array();
  
    do{
      $start = $page * $n;
      //print "$start\n";
      
      $json = $this->get_data('http://api.npr.org/query', array(
        'id' => $query,
        'fields' => 'title,storyDate,text',
        'numResults' => $n,
        'startNum' => ($n * $page) + 1,
        'apiKey' => Config::get('NPR'),
        'output' => 'JSON',
      ));
    
      //debug($json);
    
      if (!is_object($json) || empty($json->list->story))
        break;
      
      foreach ($json->list->story as $item)
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%d.js', $this->output_dir, $this->base64_encode_file($item->id)), json_encode($item)); 
        else
          $items[] = $item;
    
      $page++;

      //sleep(1);
    } while (1);
  
    return $items;
  }
}