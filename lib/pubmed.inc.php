<?php

class PubMed {
  function _construct(){}

  function search($q, $params = array()){
    unset($this->count, $this->webenv, $this->querykey);

    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'usehistory' => 'y',
      'retmax' => 1,
      'term' => $q,
      );

    $xml = get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array_merge($default, $params), 'xml');

    debug($xml);
    if (!is_object($xml))
      return false;

    $this->count = (int) $xml->Count;
    $this->webenv = (string) $xml->WebEnv;
    $this->querykey = (int) $xml->QueryKey;

    return $xml;
  }

  function fetch($ids = NULL, $params = array()){
    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      );
      
    if (!empty($ids)){
      $default['id'] = implode(',', is_array($ids) ? $ids : array($ids));
    }
    else if ($this->webenv){
      $default['query_key'] = $this->querykey;
      $default['WebEnv'] = $this->webenv;
    }

    return get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array_merge($default, $params), 'xml');
  }
}
