<?php

class Wikipedia extends API {
  public $doc = 'http://en.wikipedia.org/w/api.php';
  
  function search($q, $params = array()){
    $default = array(
      'action' => 'opensearch',
      'format' => 'json',  
      'redirects' => 'true',
      'limit' => 10,
      'search' => $q,
    );

    $this->get_data('http://en.wikipedia.org/w/api.php', array_merge($default, $params), 'json', $http);
    $this->results = $this->data[1];
  }
  

  function categories($title){
    if (is_array($title)) // fetch multiple titles at once
      $title = implode('|', $title);
      
    $http = array('header' => 'User-Agent: libapi'); 

    $this->get_data('http://en.wikipedia.org/w/api.php', array(
      'action' => 'query',
      'format' => 'json',
      'prop' => 'categories',
      'redirects' => 'true',
      'cllimit' => 500,
      'clshow' => '!hidden',
      'titles' => $title,
      ), 'json', $http);

    $categories = array();

    foreach ($this->data->query->pages as $id => $data){
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

  function content($title){
    $this->get_data('http://en.wikipedia.org/w/api.php', array(
      'action' => 'parse',
      'redirects' => 'true',
      'prop' => 'text|categories|displaytitle',
      'page' => $title,
      'format' => 'json',
    ));

    if (!$this->data->parse->text)
      return FALSE;

    $categories = array();
    foreach ($this->data->parse->categories as $category)
      $categories[] = $category->{'*'};

    return array(
      'title' => $this->data->parse->displaytitle, 
      'html' => $this->data->parse->text->{'*'}, 
      'categories' => $categories
      );
  }  
}