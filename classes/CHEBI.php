<?php

class CHEBI extends API {
    public $doc = 'http://www.ebi.ac.uk/chebi/webServices.do';
    public $cache = TRUE;
        
    function search($term, $start = 0, $n = 10){        
      $params = array(
        'search' => '"' . $term . '"',
        'searchCategory' => 'ALL',
        'maximumResults' => $n,
        'stars' => 'THREE ONLY',
        );

      $this->soap('http://www.ebi.ac.uk/webservices/chebi/2.0/webservice?wsdl', 'getLiteEntity', $params);     
      // TODO: check for errors
      $this->items = $this->data->return->ListElement;
      $this->total = count($this->items);
      return $this->items;
    }
    
    function fetch($ids){
      $params = array('ListOfChEBIIds' => $ids);
      $this->soap('http://www.ebi.ac.uk/webservices/chebi/2.0/webservice?wsdl', 'getCompleteEntityByList', $params);  
      $this->items = array();
      foreach ($this->data->return as $item)
        $this->items[] = $this->parse_item($item);
      return $this->items;
    }
    
    function parse_item($item){
      $properties = mol2stdinchi($item->ChemicalStructures[0]->structure);
      $item->stdinchi = $properties['iupac:stdinchi'];
      $item->stdinchikey = $properties['iupac:stdinchikey'];
      return $item;
    }
}