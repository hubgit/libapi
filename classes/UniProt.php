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
      
      $args['name'] = preg_replace('/(\+|\-|\&\&|\|\||\!|\(|\)|\{|\}|\[|\]|\^|\"|\~|\*|\?|\:|\\\\)/', '\\\\$1', $args['name']);
        
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
  
  function search_list($args, $params = array()){
    unset($this->total, $this->data, $this->results);
    
    $default = array(
      'query' => $this->build_term($args),
      'sort' => 'score',
      'limit' => 10,
      'format' => 'list',
      );

    $params = array_merge($default, $params);
    $this->get_data($this::$server, $params, 'raw');
    
    $ids = explode("\n", trim($this->data));    
    return empty($ids) ? FALSE : $this->fetch($ids);
  }
  
  function fetch($ids){
    $dom = $this->get_data('http://www.ebi.ac.uk/cgi-bin/dbfetch', array(
      'db' => 'uniprot',
      'id' => implode(',', $ids),
      'format' => 'uniprotxml',
      'style' => 'raw',
      ), 'dom');
    
    $this->xpath->registerNamespace('u', 'http://uniprot.org/uniprot');
    $nodes = $this->xpath->query('u:entry');
    
    if (empty($nodes))
      return FALSE;
    
    $items = array();
    foreach ($nodes as $node){
      $id = $this->xpath->query('u:accession', $node)->item(0)->textContent;
      
      $keywords = array();
      foreach ($this->xpath->query("u:keyword", $node) as $item)
        $keywords[] = $item->textContent;
        
      $synonyms = array();
      foreach ($this->xpath->query("u:protein/u:recommendedName/u:shortName", $node) as $item)
        $synonyms[] = $item->textContent;
      foreach ($this->xpath->query("u:protein/*[local-name()='alternativeName' or local-name()='submittedName']/*[local-name()='fullName' or local-name()='shortName']", $node) as $item)
        $synonyms[] = $item->textContent;
      
      $genes = array();
      //foreach ($this->xpath->query("u:gene/u:name[@type='primary']", $node) as $item)
      foreach ($this->xpath->query("u:gene/u:name", $node) as $item)
        $genes[] = $item->textContent;
        
      $items[$id] = array(
        'uniprot:id' => $id,
        'uniprot:name' => $this->xpath->query("u:name", $node)->item(0)->textContent,
        'dc:title' => $this->xpath->query("u:protein/u:recommendedName/u:fullName", $node)->item(0)->textContent,
        'uniprot:genes' => $genes,
        'bio:organism' => array(
          'common' => $this->xpath->query("u:organism/u:name[@type='common']", $node)->item(0)->textContent,
          'scientific' => $this->xpath->query("u:organism/u:name[@type='scientific']", $node)->item(0)->textContent,
          ),
        'x:synonyms' => $synonyms,
        );
    }
    
    return $items;      
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
    
    $items = $this->fetch(array_keys($this->results));
    foreach ($items as $id => $item)
      foreach ($item as $key => $value)
        $this->results[$id][$key] = $value;
        
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
