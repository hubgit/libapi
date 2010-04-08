<?php

class NPR extends API {
  public $doc = 'http://www.npr.org/api/'; # Note that full-text content is not available for some topics.
  public $def = 'NPR';
  
  public $n = 20;
  
  function content($npr_topic){ 
    if (!is_numeric($npr_topic))
      return FALSE;
  
    $page = 0; // results start at 0
      
    do{
      $start = $page * $this->n;
      
      $this->get_data('http://api.npr.org/query', array(
        'id' => $npr_topic,
        'fields' => 'title,storyDate,text',
        'numResults' => $this->n,
        'startNum' => $start + 1,
        'apiKey' => Config::get('NPR'),
        'output' => 'JSON',
      ));
    
      if (empty($this->data->list->story))
        break;
      
      foreach ($this->data->list->story as $item)
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%d.js', $this->output_dir, $this->base64_encode_file($item->id)), json_encode($item)); 
        else
          $this->results[] = $item;
    
      $page++;

      //sleep(1);
    } while (1);
  }
}