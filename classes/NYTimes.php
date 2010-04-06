<?php

class NYTimes extends API {
  public $doc = 'http://developer.nytimes.com/docs/article_search_api';
  public $def = 'NYTIMES';
  
  public $results = array();
  public $total;

  function content($query, $params = array()){    
    if ($params['nytimes_facet'])
      $query = sprintf('des_facet:[%s]', $params['nytimes_facet']);
  
    $n = 10;
    $page = 0; // results start at 0
  
    $daily_limit = 5000;
  
    $items = array();
  
    do{
      $start = $page * $n;
      
      $this->get_data('http://api.nytimes.com/svc/search/v1/article', array(
        'query' => $query,
        'fields' => 'byline,body,date,title,url,des_facet',
        'api-key' => Config::get('NYTIMES'),
        'offset' => $page,
      ));
    
      if (empty($this->data->results))
        break;
      
      $this->total = $this->data->total;
      
      foreach ($this->data->results as $item){
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%s.js', $this->output_dir, $this->base64_encode_file($item->url)), json_encode($item)); 
        else
          $this->results[] = $item;
      }

      sleep(1);
    } while ($start < $this->data->total && ++$page < $daily_limit);
  }
  
  function search($q, $params = array()){
    $default = array(
      'query' => $q,
      'fields' => 'byline,body,date,title,url,des_facet',
      'api-key' => Config::get('NYTIMES'),
    );

    $this->get_data('http://api.nytimes.com/svc/search/v1/article', array_merge($default, $params));
    
    $this->results = $this->data->results;
    $this->total = $this->data->total;
  }
}
