<?php

class ChemSpider extends API {
    public $doc = 'http://www.chemspider.com/AboutServices.aspx';
    public $def = 'CHEMSPIDER';

    function InChIKeyToCSID($inchikey){
      $xml = $this->get_data('http://www.chemspider.com/InChI.asmx/InChIKeyToCSID', array('inchi_key' => $inchikey), 'xml');

      debug($xml);
      if (!is_object($xml))
        return FALSE;

      return (int) $xml;
    }
    
    function InChIToCSID($inchi){
      $xml = $this->get_data('http://www.chemspider.com/InChI.asmx/InChIToCSID', array('inchi' => $inchi), 'xml');

      debug($xml);
      if (!is_object($xml))
        return FALSE;

      return (int) $xml;
    }
    
    function CSID2ExtRefs($args){
      $this->validate($args, 'csid', array('datasources' => array('Wikipedia'))); extract($args);      

      $params = array(
        'csid' => $csid,
        'token' => Config::get('CHEMSPIDER'),
        'datasources' => implode(',', $datasources),
        );
        
      $xml = $this->get_data('http://www.chemspider.com/Search.asmx/CSID2ExtRefs', $params, 'xml');

      debug($xml);
      if (!is_object($xml))
        return FALSE;

      $items = array();
      foreach ((array) $xml->ExtRef as $key => $value)
        $items[$key] = (string) $value;
      
      return $items;
    }
    
    function GetStructureSynonyms($mol){
      $params = array(
        'mol' => $mol,
        'token' => Config::get('CHEMSPIDER'),
        );
        
      $xml = $this->get_data('http://www.chemspider.com/Synonyms.asmx/GetStructureSynonyms', $params, 'xml');
      debug($xml); exit();
      if (!is_object($xml))
        return FALSE;
      
      $items = array();
      foreach ($xml->string as $item)
        $items[] = (string) $item;
        
      return $items;
    }
    
    function search($args){
      $this->validate($args, 'query'); extract($args);  
          
      $params = array(
        'query' => $query,
        'token' => Config::get('CHEMSPIDER'),
        );
        
      $xml = $this->get_data('http://www.chemspider.com/Search.asmx/SimpleSearch', $params, 'xml');

      debug($xml);
      if (!is_object($xml))
        return FALSE;

      $items = array();
      foreach ($xml->int as $value)
        $items[] = (int) $value;
      
      return $items;
    }
    
    function image($csid){
      debug('CSID: ' . $csid);
      $params = array(
        'id' => $csid,
        'token' => Config::get('CHEMSPIDER'),
        );

      $this->output_dir = $this->get_output_dir('cache/chemspider/images');
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
      return base64_decode((string) $this->get_data('http://www.chemspider.com/Search.asmx/GetCompoundThumbnail', $params, 'xml'));
    }
}