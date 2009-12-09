<?php

class NYTimes extends API {
 public $doc = 'http://developer.nytimes.com/docs/article_search_api';
 public $def = 'NYTIMES';

  function content($q){
    if (isset($q['nytimes-facet']))
      $query = sprintf('des_facet:[%s]', $q['nytimes-facet']);
    
    if (!isset($query))
      return FALSE;
    
    if (isset($q['output']))
      $output_dir = output_dir($q['output']);
  
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
        if ($output_dir)
          file_put_contents(sprintf('%s/%s.js', $output_dir, $this->base64_encode_file($item->url)), json_encode($item)); 
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
