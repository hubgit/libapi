<?php

class CHEBI extends BioPortal {
    public $doc = 'http://www.ebi.ac.uk/chebi/webServices.do';
    public $cache = TRUE;
    
    public $wsdl = 'http://www.ebi.ac.uk/webservices/chebi/2.0/webservice?wsdl';
    public $n = 10;
    
    function search($term, $field = 'ALL'){
      $params = array(
        'search' => $term,
        'searchCategory' => $field,
        'maximumResults' => $this->n,
        'stars' => 'THREE ONLY',
        );

      $this->soap($this->wsdl, 'getLiteEntity', $params);     
      $this->items = $this->data->return->ListElement;
      $this->total = count($this->items);
      
      return $this->items;
    }
    
    function fetch($ids){
      $params = array('ListOfChEBIIds' => $ids);
      $this->soap($this->wsdl, 'getCompleteEntityByList', $params);  
        
      $items = array();
      foreach ($this->data->return as $item){
        $data = $this->parse_item($item);
        $items[$data->chebiId] = $data;
      }
      
      // re-sort the items, as getCompleteEntityByList doesn't maintain the original order
      $this->items = array();
      foreach ($ids as $id)
        if (!empty($items[$id]))
          $this->items[$id] = $items[$id];

      return $this->items;
    }
    
    function parse_item($item){
      $properties = mol2stdinchi($item->ChemicalStructures[0]->structure);
      $item->stdinchi = $properties['iupac:stdinchi'];
      $item->stdinchikey = $properties['iupac:stdinchikey'];
      return $item;
    }
    
    function annotate($text){
      parent::annotate($text, array(42878)); // 42878 = version-specific localOntologyId from http://rest.bioontology.org/obs/ontologies 1007 = virtualOntologyId
      foreach ($this->annotations as &$annotation)
        $annotation['type'] = 'chemical';
        
      // TODO: lookup structures and calculate stdinchi + stdinchikey for each one
    }
}