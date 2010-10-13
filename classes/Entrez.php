<?php

class Entrez extends API{
  public $doc = 'http://eutils.ncbi.nlm.nih.gov/';
  public $cache = TRUE;
  
  public $webenv;
  public $querykey;

  public $n = 10;
  
  public $db;
  
  function build_query($args){
    return is_array($args) ? $args['dc:title'] : $args; 
  }

  function search($args, $params = array()){
    unset($this->webenv, $this->querykey, $this->total, $this->data);
    
    $term = $this->build_query($args);
    
    if (is_array($term))
      return call_user_func(array($this, $term[0]), $term[1]);

    $default = array(
      'db' => $this->db,
      'usehistory' => 'y',
      'rettype' => 'xml',
      'RetMax' => 0,
      'term' => $term,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );

    $params = array_merge($default, $params);
    $this->soap('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/eutils.wsdl', 'run_eSearch', $params);
    //debug($this->data);

    $this->total = $this->data->Count;
    
    if ($params['usehistory'] == 'y'){
      $this->webenv = $this->data->WebEnv;
      $this->querykey = $this->data->QueryKey;
    }
    
    return $this->data;
  }

  function fetch($ids = NULL, $params = array(), $method = 'run_eFetch'){
     $default = array(
      'db' => $this->db,
      'retmode' => 'xml',
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );

    if (!empty($ids)){
      $default['id'] = implode(',', is_array($ids) ? $ids : array($ids));
    }
    else if ($this->webenv){
      $default['query_key'] = $this->querykey;
      $default['WebEnv'] = $this->webenv;
    }
    else
      throw new Exception('No IDs or query history to fetch');
    
    switch ($this->db){
      //case 'pccompound':
      //case 'pcsubstance':
      //$wsdl = 'efetch_pubchem';
      //break;
      
      case 'pubmed':
        $wsdl = 'efetch_pubmed';
      break;
      
      case 'gene':
        $wsdl = 'eutils'; // using eSummary not eFetch
      break;
      
      default:
        throw new Exception('No SOAP eFetch interface for this database');
      break; 
    }
    
    $this->soap(sprintf('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/%s.wsdl', $wsdl), $method, array_merge($default, $params));
    return $this->parse();
  }
  
  function parse(){
    return $this->data; 
  }
}
