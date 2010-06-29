<?php

class UniProt extends API{
  public $doc = 'http://www.uniprot.org/uniprot/';
  
  static $server = 'http://www.uniprot.org/uniprot/';
  
  function build_term($args){
    if ($args['id'])
      $args['term'] = sprintf('id:%s', $args['id']);
    else if ($args['name'])
      $args['term'] = sprintf('"%s"', $args['name']);

    if (!$term = $args['term'])
      return FALSE;

    return $term;
  }
  
  function search($args, $params = array()){
    unset($this->total, $this->data);
    
    $term = $this->build_term($args);

    $default = array(
      'query' => $term,
      'sort' => 'score',
      'limit' => 10,
      'format' => 'tab',
      'columns' => 'id,entry name,protein names,genes,organism',
      );

    $params = array_merge($default, $params);
    $this->get_data($this::$server, $params, 'raw');
    
    if (!isset($this->data))
      throw new Exception('Error searching UniProt');
    
    $lines = explode("\n", $this->data);
    $headings = array_shift($lines);
    
    $this->results = array();
    foreach ($lines as $line){
      if (empty($line))
        continue;
        
      $item = explode("\t", $line);
      $this->results[$item[0]] = array(
        'id' => $item[0],
        'name' => $item[1],
        'synonyms' => $item[2],
        'genes' => explode(' ', $item[3]),
        'organism' => $item[4],
        ); 
    }
        
      debug($this->results);

  }
}
