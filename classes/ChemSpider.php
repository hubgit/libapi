<?php

class ChemSpider extends API {
    public $doc = 'http://www.chemspider.com/AboutServices.aspx';
    public $def = 'CHEMSPIDER';
    
    function InChIKeyToCSID($inchikey){
      $params = array('inchi_key' => $inchikey);
      
      $client = new SoapClient('http://www.chemspider.com/inchi.asmx?wsdl');
      $this->response = $client->InChIKeyToCSID($params);
      return $this->response->InChIKeyToCSIDResult;
      
      //$this->get_data('http://www.chemspider.com/InChI.asmx/InChIKeyToCSID', $params, 'xml');
      //return (int) $this->data;
    }
    
    function InChIToCSID($inchi){
      $params = array('inchi' => $inchi);
      
      $client = new SoapClient('http://www.chemspider.com/inchi.asmx?wsdl');
      $this->response = $client->InChIToCSID($params);
      return $this->response->InChIToCSIDResult;
      
      //$this->get_data('http://www.chemspider.com/InChI.asmx/InChIToCSID', $params, 'xml');
      //return (int) $this->data;
    }
    
    function CSID2ExtRefs($csid, $datasources = array('wikipedia')){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'CSID' => $csid,
        //'datasources' => implode(',', $datasources),
        'datasources' => $datasources,
        );
      
      $client = new SoapClient('http://www.chemspider.com/Search.asmx?wsdl');
      $this->response = $client->CSID2ExtRefs($params);
      return $this->response->CSID2ExtRefsResult->ExtRef;
        
      //$this->get_data('http://www.chemspider.com/Search.asmx/CSID2ExtRefs', $params, 'xml');

      //$items = array();
      //foreach ($this->data->ExtRef as $key => $value)
        //$items[$key] = (string) $value;
      
      //return $items;
    }
    
    function GetStructureSynonyms($mol){
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'mol' => $mol,
        );
        
      $client = new SoapClient('http://www.chemspider.com/Synonyms.asmx?wsdl');
      $this->response = $client->GetStructureSynonyms($params);
      return $this->response->GetStructureSynonymsResult->string;
        
      //$this->get_data('http://www.chemspider.com/Synonyms.asmx/GetStructureSynonyms', $params, 'xml');
      
      //$items = array();
      //foreach ($xml->string as $item)
        //$items[] = (string) $item;
        
      //return $items;
    }
    
    function search($term){        
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'query' => $term,
        );
      
      $client = new SoapClient('http://www.chemspider.com/Search.asmx?wsdl');
      //debug($client->__getTypes());
      $this->response = $client->SimpleSearch($params);
      return $this->response->SimpleSearchResult->int;
      
      //$this->get_data('http://www.chemspider.com/Search.asmx/SimpleSearch', $params, 'xml');        
        
      //foreach ($this->data->int as $value)
        //$this->results[] = (int) $value;
    }
    
    function image($csid){
      debug('CSID: ' . $csid);
      $params = array(
        'id' => $csid,
        'token' => Config::get('CHEMSPIDER'),
        );

      $this->output_dir = $this->get_output_dir('cache/chemspider/images');
      ksort($params);
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

    // http://www.chemspider.com/ImagesHandler.ashx?id={$csid}
    function get_image($params){
      $client = new SoapClient('http://www.chemspider.com/Search.asmx?wsdl');
      $this->response = $client->GetCompoundThumbnail($params);
      return $this->response->GetCompoundThumbnailResult;
      //return base64_decode((string) $this->get_data('http://www.chemspider.com/Search.asmx/GetCompoundThumbnail', $params, 'xml'));
    }
}