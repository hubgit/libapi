<?php

class ChemSpider extends API {
    public $doc = 'http://www.chemspider.com/AboutServices.aspx';
    public $def = 'CHEMSPIDER';
    public $cache = TRUE;
    
    public $n = 10;
    
    function build_query($args){
      if ($args['dc:title'])
        return array('SimpleSearch', $args['dc:title']);
      if ($args['iupac:name'])
        return array('SimpleSearch', $args['iupac:name']);
      else if ($args['iupac:stdinchi'])
        return array('SimpleSearch', $args['iupac:stdinchi']);
      else if ($args['iupac:stdinchikey'])
        return array('SimpleSearch', $args['iupac:stdinchikey']);
      else if ($args['iupac:inchi'])
        return array('SimpleSearch', $args['iupac:inchi']);
      else if ($args['iupac:inchikey'])
        return array('SimpleSearch', $args['iupac:inchikey']);
      else if ($args['chemspider:id'])
        return array('SimpleSearch', $args['chemspider:id']);
      else if ($args['chem:molecular-formula'])
        return array('SearchByFormula2', $args['chem:molecular-formula']);
    }
    
    function search($args, $params = array(), $start = 0){
      unset($this->total);
      $term = $this->build_query($args);
      
      $ids = call_user_func(array($this, $term[0]), $term[1]);     
      $this->total = count($ids);  
      $ids = array_slice($ids, $start, $this->n);
                
      if (!empty($ids))
        return array_map(array($this, 'fix_search_items'), $this->GetExtendedCompoundInfoArray($ids));
    }
    
    function fix_search_items($item){
      $data = array(
        'chemspider:id' => $item->CSID,
        'chem:molecular-formula' => preg_replace('/_\{(\d+)\}/', '$1', $item->MF),
        'chem:smiles' => $item->SMILES,
        'iupac:inchi' => $item->InChI,
        'iupac:inchikey' => $item->InChIKey,
        'chem:molecular-weight' => $item->MolecularWeight,
        'dc:title' => $item->CommonName,
        'misc:image' => url('http://www.chemspider.com/ImagesHandler.ashx', array('w' => 200, 'h' => 200, 'id' => $item->CSID)),
        'rdf:uri' => url('http://www.chemspider.com/' . urlencode($item->CSID)),
      ); 
      
      if ($data['iupac:inchi']){
        $properties = mol2stdinchi($data['iupac:inchi']);
        $data['iupac:stdinchi'] = $properties['iupac:stdinchi'];
        $data['iupac:stdinchikey'] = $properties['iupac:stdinchikey'];
      }

      return $data;
    }
    
    function SimpleSearch($term){        
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'query' => $term,
        );

      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'SimpleSearch', $params);  
      return $this->data->SimpleSearchResult->int;
    }
    
    function SearchByFormula2($formula){
      $params = array(
        //'token' => Config::get('CHEMSPIDER'),
        'formula' => $formula,
        );

      $this->soap('http://www.chemspider.com/MassSpecAPI.asmx?wsdl', 'SearchByFormula2', $params);
      debug($this->data);
      return $this->data->SearchByFormula2Result->string;
    }
    
    function AsyncSimpleSearch($term){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'query' => $term,
        );
      
      $this->cache = FALSE;
      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'AsyncSimpleSearch', $params);    
      debug($this->data); 
      $rid = $this->data->AsyncSimpleSearchResult;
      
      if (!$rid)
        throw new HTTPException(500, 'Error searching ChemSpider: rid = ' . $rid);
      
      $status = NULL;
      
      $i = 0;
      while ($i++ < 10) { // try 12 times, every 5 seconds
        $params = array(
          'token' => Config::get('CHEMSPIDER'),
          'rid' => $rid,
          );

        $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'GetAsyncSearchStatus', $params); 
        debug($this->data);
        
        $status = $this->data->GetAsyncSearchStatusResult;
        if (!in_array($status, array('Created', 'Scheduled', 'Processing')))
          break;
          
        sleep(5); 
      }
      
      if ($status != 'ResultReady')
        throw new HTTPException(500, 'Error searching ChemSpider; status = ' . $status);
      
      $sdf = $this->GetRecordsSdf($rid);
      $items = parse_sdf($sdf); 
      $items = array_map(array($this, 'fix_search_items'), $items);
      return $items;
    }

    function fix_search_items_sdf($item){
      $item['chemspider:id'] = $item['meta-CSID'];
      unset($item['meta-CSID']);
      
      $item['misc:image'] = url('http://www.chemspider.com/ImagesHandler.ashx', array('id' => $item['chemspider:id'], 'w' => 200, 'h' => 200));
      $item['rdf:uri'] = url('http://www.chemspider.com/' . urlencode($item['chemspider:id']));
      return $item;
    }
    
    function GetRecordsSdf($rid){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'rid' => $rid,
        );

      $this->soap('http://www.chemspider.com/MassSpecAPI.asmx?wsdl', 'GetRecordsSdf', $params);
      return $this->data->GetRecordsSdfResult;
    }
    
    function InChIKeyToCSID($inchikey){      
      $this->soap('http://www.chemspider.com/inchi.asmx?wsdl', 'InChIKeyToCSID', array('inchi_key' => $inchikey));
      return $this->data->InChIKeyToCSIDResult;
    }
    
    function InChIToCSID($inchi){    
      $this->soap('http://www.chemspider.com/inchi.asmx?wsdl', 'InChIToCSID', array('inchi' => $inchi));
      return $this->data->InChIToCSIDResult;
    }
    
    function Mol2CSID($mol, $options = 'eAllTautomers'){
      // $options = eExactMatch or eAllTautomers or eSameSkeletonAndH or eSameSkeleton or eAllIsomers
      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'Mol2CSID', array('mol' => $mol, 'options' => $options));
      return $this->data->Mol2CSIDResult;
    }
    
    function CSID2ExtRefs($csid, $datasources = array('wikipedia')){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'CSID' => $csid,
        'datasources' => $datasources,
        );
      
      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'CSID2ExtRefs', $params);     
      return $this->data->CSID2ExtRefsResult->ExtRef;
    }
    
    function GetStructureSynonyms($mol){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'mol' => $mol,
        );
        
      $this->soap('http://www.chemspider.com/synonyms.asmx?wsdl', 'GetStructureSynonyms', $params);     
      return $this->data->GetStructureSynonymsResult->string;
    }
    
    function GetCompoundInfo($id){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'CSID' => $id,
        );
        
      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'GetCompoundInfo', $params);     
      return $this->data->GetCompoundInfoResult;
    }
    
    function GetExtendedCompoundInfoArray($ids){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'CSIDs' => $ids,
        );

      $this->soap('http://www.chemspider.com/MassSpecAPI.asmx?wsdl', 'GetExtendedCompoundInfoArray', $params);
      return $this->data->GetExtendedCompoundInfoArrayResult->ExtendedCompoundInfo;      
    }
    
    
    // or http://www.chemspider.com/ImagesHandler.ashx?id={$csid}
    function get_image($params){
      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'GetCompoundThumbnail', $params);     
      return $this->data->GetCompoundThumbnailResult;
    }
    
    function image($csid){
      $params = array(
        'id' => $csid,
        'token' => Config::get('CHEMSPIDER'),
        );

      ksort($params);
      $this->output_dir = $this->get_output_dir('cache/chemspider/images');
      $file = sprintf('%s/%s.png', $this->output_dir, $this->base64_encode_file(http_build_query($params)));

      if (!file_exists($file))
        file_put_contents($file, $this->get_image($params));

      if (file_exists($file)){
        header('Content-Type: image/png');
        //header('Content-Length: ' . filesize($file));
        readfile($file);
      }
      else{
        // default image
      }
    }
    
    function autocomplete($text, $n = 10){
      $this->soap('http://www.chemspider.com/AutoComplete.asmx?wsdl', 'GetSynonymsSuggestions', array('prefixText' => $text, 'count' => $n));
      return $this->data->GetSynonymsSuggestionsResult->string;
    }
    
    function autocomplete_json($text, $n = 10){
      $content = json_encode(array('prefixText' => $text, 'count' => $n));
      $headers = array(
       'Content-Type: application/json; charset=utf-8',
       'Content-Length: ' . strlen($content),
       );
      $http = array('method' => 'POST', 'header' => implode("\n", $headers), 'content' => $content);
      $this->get_data('http://www.chemspider.com/AutoComplete.asmx/GetSynonymsSuggestions', NULL, 'json', $http);
      return $this->data->d;
    }
}