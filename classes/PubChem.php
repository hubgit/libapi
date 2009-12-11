<?php

class PubChem extends API{
  function search($q){
    unset($this->count, $this->webenv, $this->querykey);
    
    $this->db = 'pccompound';
    
    if ($q['cid'])
      $q['term'] = sprintf('%d[CID]', $q['cid']);
    else if ($q['sid'])
      $q['term'] = sprintf('%d[SID]', $q['sid']);
    else if ($q['inchikey'])
      $q['term'] = sprintf('"%s"[InChIKey]', preg_replace('/^inchikey=/i', '', $q['inchikey']));
    else if ($q['inchi'])
      return $this->pug($q['inchi']); 
      
    if (!$term = $q['term'])
      return FALSE;
      
    if ($q['sid'])
      $this->db = 'pcsubstance';
      
    debug($term);
  
    // put free text queries in quotes
    if (strpos($term, '"') === FALSE && !preg_match('/\[[CS]ID\]/', $term))
      $term = sprintf('"%s"', $term);

    $params = array(
      'db' => $this->db,
      'term' => $term,
      'retmax' => 1,
      'retmode' => 'xml',
      'usehistory' => 'y',
      );
      
    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', $params, 'xml');

    if (!is_object($xml))
      exit('Error searching PubChem');

    $this->count = (int) $xml->Count;
    $this->webenv = (string) $xml->WebEnv;
    $this->querykey = (int) $xml->QueryKey;
    
    return $xml;
  }
  
  function fetch($ids = NULL, $params = array()){
    $default = array(
      'db' => $this->db,
      'retmode' => 'xml',
      );
      
    if (!empty($ids)){
      $default['id'] = implode(',', is_array($ids) ? $ids : array($ids));
    }
    else if ($this->webenv){
      $default['query_key'] = $this->querykey;
      $default['WebEnv'] = $this->webenv;
    }

    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', array_merge($default, $params), 'xml');
    //debug($xml);

    if (!is_object($xml) || isset($xml->ERROR))
      return FALSE;

    $items = array();
    foreach ($xml->DocSum as $item)
      $items[] = $item;

    return $items; 
  }
  
  function parse($doc){
    debug($doc);
     $result = array(
       'id' => (int) $doc->Id,
       'synonyms' => array(),
       'meshheadings' => array(),
       'name' => '',
       'raw' => $doc,
       );

     foreach ($doc->Item as $item){
       switch ($item['Type']){
         case 'List':
           switch ($item['Name']){
             case 'SynonymList':
               foreach ($item->Item as $subitem)
                 $result['synonyms'][] = (string) $subitem;
             break;

             case 'MeSHHeadingList':
               foreach ($item->Item as $subitem)
                 $result['meshheadings'][] = (string) $subitem;
             break;
           }
         break;

         case 'String': case 'Integer': default:
           $result[(string) $item['Name']] = (string) $item;   
         break;
       }
     }
     
     if (!empty($result['synonyms']))
       $result['name'] = $result['synonyms'][0];
     else if (!empty($result['IUPACName']))
       $result['name'] = $result['IUPACName'];
     else if (!empty($result['meshheadings']))
       $result['name'] = implode(' | ', $result['meshheadings']);

     return $result;
   }
  
  function pug($inchi){
    if (stripos($inchi, 'inchi=') !== 0)
      $inchi = 'InChI=' . $inchi;
    
    $xml = sprintf(file_get_contents(Config::get('MISC_DIR') . '/pubchem/pug-inchi.xml'), htmlspecialchars($inchi));
    $http = array('method'=> 'POST', 'content' => $xml, 'header' => 'Content-Type: text/xml; charset=UTF-8');
    $result = $this->get_data('http://pubchem.ncbi.nlm.nih.gov/pug/pug.cgi', array(), 'dom', $http);
    //debug($result->saveXML());
    
    $xpath = new DOMXPath($result);
    $status = $xpath->query("//PCT-Status")->item(0)->getAttribute("value");
    debug('Status: ' . $status);
    if ($status != 'queued')
      exit('Error searching PubChem');
      
    $reqid = $xpath->query("//PCT-Waiting_reqid")->item(0)->nodeValue;
    $xml = sprintf(file_get_contents(Config::get('MISC_DIR') . '/pubchem/pug-reqid.xml'), htmlspecialchars($reqid));

    $i = 0;
    do { // try 10 times to connect, every 6 seconds
      $http = array('method'=> 'POST', 'content' => $xml, 'header' => 'Content-Type: text/xml; charset=UTF-8');
      $result = $this->get_data('http://pubchem.ncbi.nlm.nih.gov/pug/pug.cgi', array(), 'dom', $http);

      $xpath = new DOMXPath($result);
      $status = $xpath->query("//PCT-Status")->item(0)->getAttribute("value");
      debug('Status: ' . $status);
      if ($status == 'success')
        break;

      sleep(6);
    } while ($i++ < 10);

    if ($i == 10)
      exit('Timed out fetching results from PubChem');
   
    debug($result->saveXML());
    $nodes = $xpath->query("//PCT-Entrez");
    if (!$nodes->length)
      return FALSE;
      
    $node = $nodes->item(0);
    
    $this->db = $xpath->query('PCT-Entrez_db', $node)->item(0)->nodeValue;
    $this->count = $xpath->query('PCT-Entrez_count', $node)->item(0)->nodeValue;
    $this->webenv = $xpath->query('PCT-Entrez_webenv', $node)->item(0)->nodeValue;
    $this->querykey = $xpath->query('PCT-Entrez_query-key', $node)->item(0)->nodeValue;
    
    return $node;
  }
  
  function image($params){
    $default = array(
      'width' => 100,
      'height' => 100,
      );
      
    $params = array_merge($default, $params);
    
    $this->output_dir = $this->$this->get_output_dir('cache/pubchem/images');
    $file = sprintf('%s/%s.png', $this->output_dir, $this->base64_encode_file(http_build_query($params)));
    
    if (!file_exists($file))
      file_put_contents($file, $this->get_image($params));
    
    if (file_exists($file)){
      header('Content-Type: image/png');
      header('Content-Length: ' . filesize($file));
      readfile($file);
    }
    else{
      // default image
    }
  }
  
  function get_image($params){
    return $this->get_data('http://pubchem.ncbi.nlm.nih.gov/image/imagefly.cgi', $params, 'raw');
  }
}