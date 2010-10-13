<?php

class EntrezGene extends Entrez{
  public $doc = 'http://www.ncbi.nlm.nih.gov/bookshelf/br.fcgi?book=helpgene&part=EntrezGene';
  
  public $db = 'gene';
  
  function build_query($args){
    debug($args);
    if ($args['dc:title']){
      $parts = array();
      foreach (array('gene', 'title') as $field)
        $parts[] = sprintf('"%s"[%s]', $args['dc:title'], $field);
      $term = '(' . implode(' OR ', $parts) . ')';
    }
    else if ($args['gene:symbol'])
      $term = sprintf('"%s"[sym]', $args['gene:symbol']);
    else if ($args['entrezgene:id'])
      $term = sprintf('"%s"[accn]', $args['entrezgene:id']);
    else
      return false;
      
    if ($args['bio:organism'])
      $term .= sprintf(' AND "%s"[orgn]', $args['bio:organism']);
    
    $parts = array();
    foreach (array('reviewed', 'validated') as $status)
      $parts[] .= sprintf('srcdb_refseq_%s[PROP]', $status);    
    $term .= sprintf(' AND (%s)', implode(' OR ', $parts));
    
    $term .= ' AND alive[PROP]';
    
    debug($term);
    return $term;
  }
  
  function parse(){
    return $this->parse_summary(); 
  }
  
  function parse_summary(){
    $items = array();
    
    foreach ($this->data->DocSum as $item){ 
      $data = array();
      foreach ($item->Item as $i)
        $data[$i->Name] = $i->ItemContent;
        
      $synonyms = array();
      if ($data['OtherAliases'])
        $synonyms = array_merge($synonyms, explode('|', $data['OtherAliases']));
      if ($data['OtherDesignations'])
        $synonyms = array_merge($synonyms, explode('|', $data['OtherDesignations']));  
      
      $items[] = array(
        'entrezgene:id' => $item->Id,
        'dc:title' => $data['Description'],
        'gene:symbol' => $data['Name'],
        'bio:organism' => $data['Orgname'],
        'misc:synonyms' => $synonyms,
        );
    }
    
    return $items;
  }
}
