<?php

class PubChem extends Entrez{
  public $doc = 'http://pubchem.ncbi.nlm.nih.gov/'; // http://pubchem.ncbi.nlm.nih.gov/pug_soap/pug_soap_help.html
  public $def = array('EUTILS_TOOL', 'EUTILS_EMAIL');
  
  public $db = 'pccompound';
  
  public $pug_wsdl = 'http://pubchem.ncbi.nlm.nih.gov/pug_soap/pug_soap.cgi?wsdl';
  
  // http://pubchem.ncbi.nlm.nih.gov/search/help_search.html
  function build_query($args){
    $term = null;
    
    debug($args);
    
    if ($args['iupac:stdinchi'])
      return array('pug_soap_inchi', $args['iupac:stdinchi']);
    if ($args['iupac:inchi'])
      return array('pug_soap_inchi', $args['iupac:inchi']);
    if ($args['chem:molecular-formula'])
      return array('pug_soap_formula', $args['chem:molecular-formula']);
    
    if ($args['pubchem:cid'])
      $term = sprintf('%d[CID]', $args['pubchem:cid']);
    else if ($args['pubchem:sid'])
      $term = sprintf('%d[SID]', $args['pubchem:sid']);
    else if ($args['iupac:stdinchikey'])
      $term = sprintf('"%s"[InChIKey]', preg_replace('/^inchikey=/i', '', $args['iupac:stdinchikey']));
    else if ($args['iupac:name'])
      $term = sprintf('"%s"[IUPACName]', $args['iupac:name']); // TODO
    else if ($args['dc:title'])
      $term = sprintf('"%s"[All Fields]', $args['dc:title']);

    if (!$term)
      return false;

    // put free text queries in quotes
    if (strpos($term, '"') === false && !preg_match('/\[[CS]ID\]/', $term)) // && strpos($term, '[') === false)
      $term = sprintf('"%s"', $term);
    
    return $term;
  }
  
  function search($args, $params = array()){
    return $this->search_rest($args, $params); // PubChem SOAP interface to search has a bug?
  }
  
  function search_rest($args, $params = array()){
    unset($this->total, $this->data, $this->webenv, $this->querykey);
    $term = $this->build_query($args);
    
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
  
  function fetch($ids = null, $params = array()){
    return $this->fetch_rest($ids, $params); // no SOAP interface to eFetch for PubChem yet
  }

  function fetch_rest($ids = null, $params = array()){    
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

    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', array_merge($default, $params), 'xml');

    if (isset($this->data->ERROR))
      return false;
          
    $items = array();
    foreach ($this->data->DocSum as $item)
      $items[] = $this->parse_xml_summary($item);
      
    if ($this->total == 'multiple')
      $this->total = count($items);
        
    return $items;
  }

  function parse_xml_summary($doc){   
    $data = array();

    $mesh = array();
    $synonyms = array();

    foreach ($doc->Item as $item){
      switch ($item['Type']){
        case 'List':
        switch ($item['Name']){
          case 'SynonymList':
          foreach ($item->Item as $subitem)
            $synonyms[] = (string) $subitem;
          break;

          case 'MeSHHeadingList':
          foreach ($item->Item as $subitem)
            $mesh[] = (string) $subitem;
          break;
        }
        break;

        case 'String': 
        case 'Integer': 
        default:
          $data[(string) $item['Name']] = (string) $item;
        break;
      }
    }
    
    $name = null;
    if (!empty($mesh))
      $name = implode(' | ', $mesh);
    else if (!empty($synonyms))
      $name = $synonyms[0];
    else if (!empty($data['IUPACName']))
      $name = $data['IUPACName'];

    return array(
      'pubchem:cid' => $data['CID'],
      'dc:title' => $name,
      'iupac:name' => $data['IUPACName'],
      'misc:synonyms' => $synonyms,
      'chem:molecular-formula' => $data['MolecularFormula'],
      'chem:smiles' => $data['CanonicalSmile'],
      'iupac:stdinchi' => $data['InChI'],
      'iupac:stdinchikey' => $data['InChIKey'],
      'rdf:uri' => url('http://pubchem.ncbi.nlm.nih.gov/summary/summary.cgi', array('cid' => $data['CID'])),
      'misc:image' => url('http://pubchem.ncbi.nlm.nih.gov/image/imagefly.cgi', array('width' => 200, 'height' => 200, 'cid' => $data['CID'])),
      );
  }

  function pug_soap_inchi($inchi){
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
     
     return $this->pug_fetch_when_ready($result->ListKey);
  }
  
  function pug_soap_formula($formula){                
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
          
     return $this->pug_fetch_when_ready($result->ListKey);
  }
  
  function pug_fetch_when_ready($key){
    $this->cache = false;

    $i = 0;
    do { // try 20 times, every 3 seconds
      $result = $this->soap($this->pug_wsdl, 'GetOperationStatus', array('AnyKey' => $key));

      if (!in_array($result->status, array('eStatus_Running', 'eStatus_Queued')))
        break;

      sleep(3);
    } while ($i++ < 20);

    $this->cache = true;

    if ($result->status == 'eStatus_Success')
      return $this->pug_soap_fetch_results($key);
  }
  
  function pug_soap_fetch_results($listKey){ 
    $this->total = 0;
    $this->db = null;
    $this->webenv = null;
    $this->querykey = null;
    
     $result = $this->soap($this->pug_wsdl, 'GetEntrezKey', array(
       'ListKey' => $listKey,
       ));
       
     if (!$result->EntrezKey)
      return false;
      
     $this->total = 'multiple';
     
     $this->db = $result->EntrezKey->db;
     $this->webenv = $result->EntrezKey->webenv;
     $this->querykey = $result->EntrezKey->key;
  }

  function image($params){
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

  function get_image($params, $file = null){
    $this->get_data('http://pubchem.ncbi.nlm.nih.gov/image/imagefly.cgi', $params, 'raw');
    if ($file)
      file_put_contents($file, $this->data);
  }
}

