<?php

class UniProt extends API{
  public $doc = 'http://www.uniprot.org/';
  static $server = 'http://www.uniprot.org/uniprot/';
  public $cache = TRUE;
  
  function item($id){
    $this->get_data(sprintf('http://www.uniprot.org/uniprot/%s.xml', rawurlencode($id)), array(), 'dom');
    $this->xpath->registerNamespace('u', 'http://uniprot.org/uniprot');
  }
  
  function build_term($args){
    if ($args['id'])
      $args['term'] = sprintf('accession:%s', $args['id']);
    else if ($args['name']){
      //$query = array(sprintf('"%s"', $args['name']));
      $query = array();
      foreach (array('mnemonic', 'name', 'gene', 'family', 'keyword') as $field)
        $query[] = $field . sprintf(':"%s"', $args['name']);  
      //$query[] = sprintf('"%s"', $args['name']);   
      $args['term'] = sprintf('(%s)', implode(' OR ', $query));
      
      if ($args['organism'])
        $args['term'] .= sprintf(' AND organism:"%s"', $args['organism']);
      
      //$args['term'] .= ' AND reviewed:yes';    
    }

    if (!$term = $args['term'])
      return FALSE;

    return $term;
  }
  
  function search($args, $params = array()){
    unset($this->total, $this->data);
    
    $default = array(
      'query' => $this->build_term($args),
      'sort' => 'score',
      'limit' => 10,
      'format' => 'tab',
      'columns' => 'id,entry name,protein names,genes,organism,organism-id,reviewed,families,interpro,keywords,score',
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
        'organism-id' => $item[5],
        'reviewed' => $item[6],
        'families' => $item[7],
        'interpro' => $item[8],
        'keywords' => $item[9],
        'score' => $item[10],
        ); 
    }
        
    debug($this->results);
  }
  
  function search_minimal($term){
      $this->opensearch('http://www.uniprot.org/uniprot/', array(
      'query' => $term,
      'sort' => 'score',
      'offset' => 0,
      'limit' => 10,
      'format' => 'rss',
      ));

    $this->results = array();
    foreach ($this->xpath->query('channel/item') as $item)
      $this->results[] = array(
       'link' => $this->xpath->query('link', $item)->item(0)->textContent,
       'title' => $this->xpath->query('title', $item)->item(0)->textContent,
       'description' => $this->xpath->query('description', $item)->item(0)->textContent,
       );
  }
}
