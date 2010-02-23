<?php

class NPR extends API {
  public $doc = 'http://www.npr.org/api/'; # Note that full-text content is not available for some topics.
  public $def = 'NPR';

  function content($args){
    $this->validate($args, 'npr_topic'); extract($args);
        
    if (!is_numeric($npr_topic))
      return FALSE;
    
    if ($output)
      $this->output_dir = $this->get_output_dir($output);
  
    $n = 20;
    $page = 0; // results start at 0
    
    $items = array();
  
    do{
      $start = $page * $n;
      //print "$start\n";
      
      $json = $this->get_data('http://api.npr.org/query', array(
        'id' => $npr_topic,
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