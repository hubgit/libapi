<?php

class NYTimes extends API {
 public $doc = 'http://developer.nytimes.com/docs/article_search_api';
 public $def = 'NYTIMES';

  function content($args){    
    if ($args['nytimes_facet'])
      $args['query'] = sprintf('des_facet:[%s]', $args['nytimes_facet']);
    
    $this->validate($args, 'query'); extract($args);
    
    if ($output)
      $this->output_dir = $this->get_output_dir($output);
  
    $n = 10;
    $page = 0; // results start at 0
  
    $daily_limit = 5000;
  
    $items = array();
  
    do{
      $start = $page * $n;
      //debug($start);
      
      $json = $this->get_data('http://api.nytimes.com/svc/search/v1/article', array(
        'query' => $query,
        'fields' => 'byline,body,date,title,url,des_facet',
        'api-key' => Config::get('NYTIMES'),
        'offset' => $page,
      ));
    
      if (!is_object($json) || empty($json->results))
        break;
      
      foreach ($json->results as $item){
        if ($this->output_dir)
          file_put_contents(sprintf('%s/%s.js', $this->output_dir, $this->base64_encode_file($item->url)), json_encode($item)); 
        else
          $items[] = $item;
      }

      sleep(1);
    } while ($start < $json->total && ++$page < $daily_limit);
  
    return $items;
  }
  
  function search($q, $params = array()){
    if (!$q)
      return FALSE;

    $default = array(
      'query' => $q,
      'fields' => 'byline,body,date,title,url,des_facet',
      'api-key' => Config::get('NYTIMES'),
    );

    $json = $this->get_data('http://api.nytimes.com/svc/search/v1/article', array_merge($default, $params));

    if (!is_object($json))
      return FALSE;

    return array($json->total, $json->results);
  }
}
