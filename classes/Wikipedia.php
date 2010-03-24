<?php

class Wikipedia extends API {
  public $doc = 'http://en.wikipedia.org/w/api.php';
  
  function search($q, $params = array()){
    if (!$q)
      return FALSE;

    $default = array(
      'action' => 'opensearch',
      'format' => 'json',  
      'redirects' => 'true',
      'limit' => 10,
      'search' => $q,
    );

    $json = $this->get_data('http://en.wikipedia.org/w/api.php', array_merge($default, $params), 'json', $http);

    //debug($json);

    if (!is_array($json))
      return FALSE;

    return array($json[1]);
  }
  

  function categories($args){
    $this->validate($args, 'title'); extract($args);

    if (is_array($title)) // fetch multiple titles at once
      $title = implode('|', $title);
      
    $http = array('header' => 'User-Agent: libapi'); 

    $json = $this->get_data('http://en.wikipedia.org/w/api.php', array(
      'action' => 'query',
      'format' => 'json',
      'prop' => 'categories',
      'redirects' => 'true',
      'cllimit' => 500,
      'clshow' => '!hidden',
      'titles' => $title,
      ), 'json', $http);

    //debug($json);

    if (!is_object($json))
      return FALSE;

    $categories = array();

    foreach ($json->query->pages as $id => $data){
      $item = array(
        'id' => $data->pageid,
        'title' => $data->title,
        );

      foreach ($data->categories as $category)
        $item['categories'][] = preg_replace('/^Category:/', '', $category->title);

      $categories[$data->title] = $item;
    }

    return $categories;
  } 

  function content($args){
    $this->validate($args, 'title'); extract($args);

    $json = $this->get_data('http://en.wikipedia.org/w/api.php', array(
      'action' => 'parse',
      'format' => 'json',
      'redirects' => 'true',
      'prop' => 'text|categories|displaytitle',
      'page' => $title,
    ));

    //debug($json);

    if (!is_object($json) || !$json->parse->text)
      return array();

    $categories = array();
    foreach ($json->parse->categories as $category)
      $categories[] = $category->{'*'};

    return array(
      'title' => $json->parse->displaytitle, 
      'html' => $json->parse->text->{'*'}, 
      'categories' => $categories
      );
  }  
}