<?php

class PubChem extends API{
  public $doc = 'http://pubchem.ncbi.nlm.nih.gov/'; // http://pubchem.ncbi.nlm.nih.gov/pug_soap/pug_soap_help.html
  public $def = array('EUTILS_TOOL', 'EUTILS_EMAIL');
  
  public $db = 'pccompound';
  
  public $pug_wsdl = 'http://pubchem.ncbi.nlm.nih.gov/pug_soap/pug_soap.cgi?wsdl';
  
  // http://pubchem.ncbi.nlm.nih.gov/search/help_search.html
  function build_term($args){
    if ($args['cid'])
      $args['term'] = sprintf('%d[CID]', $args['cid']);
    else if ($args['sid'])
      $args['term'] = sprintf('%d[SID]', $args['sid']);
    else if ($args['inchikey'])
      $args['term'] = sprintf('"%s"[InChIKey]', preg_replace('/^inchikey=/i', '', $args['inchikey']));
    else if ($args['inchi'])
      return array('pug_soap_inchi', $args['inchi']);
    else if ($args['formula'])
      return array('pug_soap_formula', $args['formula']);
    //else if ($args['formula'])
      //return $this->pug($args['formula']);
    else if ($args['iupac'])
      $args['term'] = sprintf('"%s"[IUPACName]', $args['name']); // TODO
    else if ($args['name'])
      $args['term'] = sprintf('"%s"[All Fields]', $args['name']);

    if (!$term = $args['term'])
      return FALSE;

    if ($args['sid'])
      $this->db = 'pcsubstance';

    // put free text queries in quotes
    if (strpos($term, '"') === FALSE && !preg_match('/\[[CS]ID\]/', $term)) // && strpos($term, '[') === FALSE)
      $term = sprintf('"%s"', $term);

    debug($term);
    
    return $term;
  }
  
  function search_soap($args, $params = array()){
    unset($this->total, $this->webenv, $this->querykey);
    $this->db = 'pccompound'; 
    $term = $this->build_term($args);
    
    if (is_array($term))
      return call_user_func(array($this, $term[0]), $term[1]);
        
    $default = array(
      'db' => $this->db,
      'usehistory' => 'y',
      'rettype' => 'count',
      'term' => $term,
      'RetMax' => 1,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );

    $params = array_merge($default, $params);
    $this->soap('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/eutils.wsdl', 'run_eSearch', $params);
    
    $this->total = $this->data->Count;
    
    if ($params['usehistory'] == 'y'){
      $this->webenv = $this->data->WebEnv;
      $this->querykey = $this->data->QueryKey;
    }
    
    return $this->data;
  }
  
  /*
  function fetch_soap($ids = NULL, $params = array()){
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

    $this->soap('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/efetch_pubchem.wsdl', 'run_eFetch', array_merge($default, $params));
    
    return $this->data;
  }
  */
  

  function search($args, $params = array()){
    unset($this->total, $this->webenv, $this->querykey);
    $this->db = 'pccompound';
    $term = $this->build_term($args);
    
    if (is_array($term))
      return call_user_func(array($this, $term[0]), $term[1]);

    $default = array(
      'db' => $this->db,
      'term' => $term,
      'retmax' => 1,
      'retmode' => 'xml',
      'usehistory' => 'y',
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );

    $params = array_merge($default, $params);
    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', $params, 'xml');

    if (!is_object($this->data))
      throw new Exception('Error searching PubChem');

    $this->total = (int) $this->data->Count;

    if ($params['usehistory'] == 'y'){
      $this->webenv = (string) $this->data->WebEnv;
      $this->querykey = (int) $this->data->QueryKey;
    }
    
    return $this->data;
  }

  function fetch($ids = NULL, $params = array()){
    unset($this->results);
    
    $default = array(
      'db' => $this->db,
      'retmode' => 'xml',
      'retmax' => 20,
      'retstart' => 1,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );

    if (!empty($ids)){
      $default['id'] = is_array($ids) ? implode(',', $ids) : (string) $ids;
    }
    else if ($this->webenv){
      $default['query_key'] = $this->querykey;
      $default['WebEnv'] = $this->webenv;
    }
    else
      throw new Exception('No IDs or query history to fetch');

    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', array_merge($default, $params), 'xml');

    if (isset($xml->ERROR))
      return FALSE;
    
    $this->results = array();
    foreach ($this->data->DocSum as $item)
      $this->results[] = $item;
      
    return $this->results;
  }

  function parse($doc){
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

     if (!empty($result['meshheadings']))
       //$result['name'] = implode(' | ', $result['meshheadings']);
       $result['name'] = $result['meshheadings'][0];
     else if (!empty($result['synonyms']))
       $result['name'] = $result['synonyms'][0];
     else if (!empty($result['IUPACName']))
       $result['name'] = $result['IUPACName'];

     return $result;
   }

  /*
  function pug($inchi){    
    if (stripos($inchi, 'inchi=') !== 0)
      $inchi = 'InChI=' . $inchi;

    $xml = sprintf(file_get_contents(Config::get('MISC_DIR') . '/pubchem/pug-inchi.xml'), htmlspecialchars($inchi));
    $http = array('method'=> 'POST', 'content' => $xml, 'header' => 'Content-Type: text/xml; charset=UTF-8');
    $this->get_data('http://pubchem.ncbi.nlm.nih.gov/pug/pug.cgi', array(), 'dom', $http);

    $status = $this->xpath->query("//PCT-Status")->item(0)->getAttribute('value');
    debug('Status: ' . $status);
    if ($status != 'queued')
      exit('Error searching PubChem');

    $reqid = $this->xpath->query("//PCT-Waiting_reqid")->item(0)->nodeValue;
    $xml = sprintf(file_get_contents(Config::get('MISC_DIR') . '/pubchem/pug-reqid.xml'), htmlspecialchars($reqid));

    $i = 0;
    do { // try 10 times to connect, every 6 seconds
      $http = array('method'=> 'POST', 'content' => $xml, 'header' => 'Content-Type: text/xml; charset=UTF-8');
      $this->get_data('http://pubchem.ncbi.nlm.nih.gov/pug/pug.cgi', array(), 'dom', $http);

      $status = $this->xpath->query("//PCT-Status")->item(0)->getAttribute("value");
      debug('Status: ' . $status);
      if ($status == 'success')
        break;

      sleep(6);
    } while ($i++ < 10);

    if ($i == 10)
      exit('Timed out fetching results from PubChem');

    debug($this->data->saveXML());
    $nodes = $this->xpath->query("//PCT-Entrez");
    if (!$nodes->length)
      return FALSE;

    $node = $nodes->item(0);

    $this->db = $this->xpath->query('PCT-Entrez_db', $node)->item(0)->nodeValue;
    $this->total = $this->xpath->query('PCT-Entrez_count', $node)->item(0)->nodeValue;
    $this->webenv = $this->xpath->query('PCT-Entrez_webenv', $node)->item(0)->nodeValue;
    $this->querykey = $this->xpath->query('PCT-Entrez_query-key', $node)->item(0)->nodeValue;

    return simplexml_import_dom($node);
  }
  */
  
  function pug_soap_inchi($inchi){
    $this->total = 0;
    $this->db = NULL;
    $this->webenv = NULL;
    $this->querykey = NULL;
        
    if (stripos($inchi, 'inchi=') !== 0)
      $inchi = 'InChI=' . $inchi;
      
    $result = $this->soap($this->pug_wsdl, 'InputStructure', array(
      'structure' => $inchi,
      'format' => 'eFormat_InChI',
      ));
                  
    $result = $this->soap($this->pug_wsdl, 'IdentitySearch', array(
      'StrKey' => $result->StrKey, 
      'idOptions' => array(
        'eIdentity' => 'eIdentity_SameIsotope',
        //'ToWebEnv' => '',
        ),
      'limits' => array(
        'seconds' => 10,
        'maxRecords' => 20,
        //'ListKey' => '',
        ),
     ));
     
     $key = $result->ListKey;
     
     //debug($result);
     
     $this->cache = FALSE;
     
     $i = 0;
     do { // try 20 times, every 3 seconds
       $result = $this->soap($this->pug_wsdl, 'GetOperationStatus', array('AnyKey' => $key));
       debug($result);
       
       if (!in_array($result->status, array('eStatus_Running', 'eStatus_Queued')))
         break;
         
       sleep(3);
     } while ($i++ < 20);
     
     $this->cache = TRUE;

     if ($result->status == 'eStatus_Success')
       return $this->pug_soap_fetch_results($key);
  }
  
  function pug_soap_formula($formula){
    $this->total = 0;
    $this->db = NULL;
    $this->webenv = NULL;
    $this->querykey = NULL;
                
    $result = $this->soap($this->pug_wsdl, 'MFSearch', array(
      'MF' => $formula, 
      'mfOptions' => array(
        'AllowOtherElements' => false,
        //'ToWebEnv' => '',
        ),
      'limits' => array(
        'seconds' => 10,
        'maxRecords' => 20,
        //'ListKey' => '',
        ),
     ));
     
     return $this->pug_soap_fetch_results($result->ListKey);
  }
  
  function pug_soap_fetch_results($listKey){     
     $result = $this->soap($this->pug_wsdl, 'GetEntrezKey', array(
       'ListKey' => $listKey,
       ));
       
     if (!$result->EntrezKey)
      return FALSE;
     
     $this->total = $result->EntrezKey ? '1+' : 0;
     $this->db = $result->EntrezKey->db;
     $this->webenv = $result->EntrezKey->webenv;
     $this->querykey = $result->EntrezKey->key;
  }

  function image($params){
    debug($params);
    $default = array(
      'width' => 100,
      'height' => 100,
      );

    $params = array_merge($default, $params);

    $this->output_dir = $this->get_output_dir('cache/pubchem/images');
    $file = sprintf('%s/%s.png', $this->output_dir, $this->base64_encode_file(http_build_query($params)));

    if (!file_exists($file))
      $this->get_image($params, $file);

    if (file_exists($file)){
      header('Content-Type: image/png');
      //header('Content-Length: ' . filesize($file));
      readfile($file);
    }
    else{
      // default image
    }
  }

  function get_image($params, $file = NULL){
    $this->get_data('http://pubchem.ncbi.nlm.nih.gov/image/imagefly.cgi', $params, 'raw');
    if ($file)
      file_put_contents($file, $this->data);
  }
}

