<?php

class XCAS {
  function __construct($xpath){
    $this->xpath = $xpath;  
  }
  
  function item($id, $name = '*'){
    debug(array($id, $name));
    return $this->xpath->evaluate(sprintf("%s[@_id='%d']", $name, $id));
  }
  
  function items($id){
    debug($id);
    return $this->xpath->evaluate(sprintf("uima.cas.FSArray[@_id='%d']/i", $id));
  }

  function knowledgesets(){
    return $this->xpath->query('com.temis.uima.KnowledgeSet');
  }

  function provider($knowledgeset){
    $nodes = $this->item($knowledgeset->getAttribute('_ref_provider'), 'com.temis.uima.KSProvider');
    return $nodes->item(0);
  }

  function elements($knowledgeset){
    return $this->items($knowledgeset->getAttribute('_ref_elements'));
  }

  function occurrences($element){
    return $this->item($element->nodeValue, 'com.temis.uima.EntityOccurrence');
  }

  function entity($occurrence){
    $nodes = $this->item($occurrence->getAttribute('_ref_entity'), 'com.temis.uima.Entity');
    return $nodes->item(0);
  }

  function concepts_by_position($occurrence){
    $items = $this->xpath->query(sprintf("com.temis.uima.IDEConcept[@begin='%d'][@end='%d']", $occurrence->getAttribute('begin'), $occurrence->getAttribute('end')));
    return $this->parse_concepts($items);
  }

  function concepts($element){
    $items = array();
    debug($element->nodeValue);
    
    $concepts = array();
    
    $nodes = $this->item($element->nodeValue, 'com.temis.uima.IDEConcept');
    if ($nodes->length){
      $concepts[] = $nodes->item(0);

      while ($nodes->item(0)->hasAttribute('_ref_child')){
        $nodes = $this->item($node->getAttribute('_ref_child'), 'com.temis.uima.IDEConcept');
        $concepts[] = $nodes->item(0);
      }
    }

    return $this->parse_concepts($concepts);
  }

  function parse_concepts($concepts){
    $items = array();
    foreach ($concepts as $concept){
      $items[] = array(
        'name' => $concept->getAttribute('name'),
        'lemma' => $concept->getAttribute('lemma'),
        'normalization' => $concept->getAttribute('normalization'),
        'form' => $concept->getAttribute('form'),
        );
    }
    return $items;
  }

  function attributes($entity){
    $items = array();

    if ($entity->hasAttribute('_ref_attributes')){
      $nodes = $this->items($entity->getAttribute('_ref_attributes'));
      foreach ($nodes as $node){
        list($name, $value) = $this->attribute($node->nodeValue);
        $items[$name] = $value;
      }
    }

    return $items;
  }
  
  function attribute($id){
    $nodes = $this->item($id);
    $node = $nodes->item(0);
    return array($node->getAttribute('name'), $node->getAttribute('value'));
  }

  function parents($entity){
    $items = array();

    while ($entity->hasAttribute('_ref_entity')){
      $nodes = $this->item($entity->getAttribute('_ref_entity'));
      $entity = $nodes->item(0);
      $items[$entity->getAttribute('value')] = 1;
    }

    return $items;
  }

}