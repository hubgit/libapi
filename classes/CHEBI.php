<?php

class CHEBI extends BioPortal {
    public $doc = 'http://www.ebi.ac.uk/chebi/webServices.do';
    public $cache = TRUE;
    
    public $wsdl = 'http://www.ebi.ac.uk/webservices/chebi/2.0/webservice?wsdl';
    public $n = 10;
    
    function build_query($args){
      if ($args['dc:title'])
        return array($args['dc:title'], 'ALL NAMES');
      else if ($args['chem:molecular-formula'])
        return array($args['chem:molecular-formula'], 'FORMULA');  
      //else if ($args['iupac:stdinchi'])
        //return array(preg_replace('/^InChI=1S/', 'InChI=1', $args['iupac:stdinchi']), 'INCHI/INCHI KEY');  
      //else if ($args['iupac:stdinchikey'])
        //return array($args['iupac:stdinchikey'], 'INCHI/INCHI KEY');
      else if ($args['iupac:inchi'])
        return array($args['iupac:inchi'], 'INCHI/INCHI KEY');  
      else if ($args['iupac:inchikey'])
        return array($args['iupac:inchikey'], 'INCHI/INCHI KEY');
      else if ($args['iupac:name'])
        return array($args['iupac:name'], 'IUPAC NAME');
      else if ($args['chem:smiles'])
        return array($args['chem:smiles'], 'SMILES');
      else if ($args['chebi:id'])
        return array($args['chebi:id'], 'CHEBI ID'); 
      // TODO: structure search using SMILES   
      else
        return false;
    }
    
    function search($args, $params = array()){
      list($term, $field) = $this->build_query($args);
      
      if (!($term && $field))
        return false;

      $defaults = array(
        'search' => $term,
        'searchCategory' => $field,
        'maximumResults' => $this->n,
        //'stars' => 'THREE ONLY',
        );
     
      $this->soap($this->wsdl, 'getLiteEntity', array_merge($defaults, $params));     
      $this->items = $this->data->return->ListElement;
      $this->total = count($this->items);
      
      return $this->items;
    }
    
    function fetch($ids){
      $this->items = array();

      switch (count($ids)){
        case 0:
        return false;
        break;
        
        case 1:
        $this->soap($this->wsdl, 'getCompleteEntity', array('chebiId' => $ids[0]));
        $result = array($this->data->return);
        break;
        
        default:
        $this->soap($this->wsdl, 'getCompleteEntityByList', array('ListOfChEBIIds' => $ids));  
        $result = $this->data->return;
        break;
      }
      
      $items = array();
      foreach ($result as $item){
        $data = $this->parse_item($item);
        $items[$data['chebi:id']] = $data;
      }
      
      debug($ids);
      
      // re-sort the items, as getCompleteEntityByList doesn't maintain the original order
      foreach ($ids as $id)
        if (!empty($items[$id]))
          $this->items[$id] = $items[$id];

      return $this->items;
    }
    
    function parse_item($item){
      $synonyms = array();
      foreach ($item->Synonyms as $name)
        $synonyms[] = $name->data;
      
      $names = array();
      foreach ($item->IupacNames as $name)
        $names[] = $name->data;
      
      $mol = null;
      if (!empty($item->ChemicalStructures)){
        foreach ($item->ChemicalStructures as $structure){
          if ($structure->defaultStructure == true){
            $mol = $structure->structure;    
          }
        }
            
        if (is_null($mol)){
          $mol = $item->ChemicalStructures[0]->structure;
        }
        
        $inchi = mol2stdinchi($mol);
      }
      
      return array(
        'chebi:id' => $item->chebiId,
        'dc:title' => $item->chebiAsciiName,
        'iupac:inchi' => $item->inchi,
        'iupac:inchikey' => preg_replace('/^InChIKey=/', '', $item->inchiKey),
        'iupac:stdinchi' => $inchi['iupac:stdinchi'],
        'iupac:stdinchikey' => $inchi['iupac:stdinchikey'],
        'chem:molecular-formula' => $item->Formulae ? $item->Formulae[0]->data : null,
        'chem:mol' => $mol,
        'chem:smiles' => $item->smiles,
        'misc:synonyms' => $synonyms,
        'iupac:name' => $names,
        'misc:image' => url('http://www.ebi.ac.uk/chebi/displayImage.do', array('defaultImage' => 'true', 'imageIndex' => 0, 'chebiId' => $item->chebiId)),
        'rdf:uri' => url('http://www.ebi.ac.uk/chebi/searchId.do', array('chebiId' => $item->chebiId)),
        );
    }
    
    function annotate($text){
      parent::annotate($text, array(42878)); // 42878 = version-specific localOntologyId from http://rest.bioontology.org/obs/ontologies 1007 = virtualOntologyId
      foreach ($this->annotations as &$annotation)
        $annotation['type'] = 'chemical';
        
      // TODO: lookup structures and calculate stdinchi + stdinchikey for each one
    }
}