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
      $this->items = array();

      switch(count($ids)){
        case 0:
        return false;
        break;
        
        case 100:
        $this->soap($this->wsdl, 'getCompleteEntity', array('chebiId' => $ids[0]));
        $result = array($this->data->return);
        break;
        
        default:
        $this->soap($this->wsdl, 'getCompleteEntityByList', array('ListOfChEBIIds' => $ids));  
        $result = $this->data->return;
        break;
      }
      
      debug($result);

      $items = array();
      foreach ($result as $item){
        $data = $this->parse_item($item);
        $items[$data->chebiId] = $data;
      }
      
      // re-sort the items, as getCompleteEntityByList doesn't maintain the original order
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