<?php

class ChemSpider extends API {
    public $doc = 'http://www.chemspider.com/AboutServices.aspx';
    public $def = 'CHEMSPIDER';
    public $cache = TRUE;
    
    function InChIKeyToCSID($inchikey){      
      $this->soap('http://www.chemspider.com/inchi.asmx?wsdl', 'InChIKeyToCSID', array('inchi_key' => $inchikey));
      return $this->data->InChIKeyToCSIDResult;
    }
    
    function InChIToCSID($inchi){    
      $this->soap('http://www.chemspider.com/inchi.asmx?wsdl', 'InChIToCSID', array('inchi' => $inchi));
      return $this->data->InChIToCSIDResult;
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
    
    function search($term){        
      $params = array(
        'token' => Config::get('CHEMSPIDER'),
        'query' => $term,
        );

      $this->soap('http://www.chemspider.com/search.asmx?wsdl', 'SimpleSearch', $params);     
      return $this->data->SimpleSearchResult->int;
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