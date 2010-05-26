<?php

class XCAS {
  function __construct($xpath){
    $this->xpath = $xpath;  
  }
  
  function item($id, $name = '*'){
    static $items = array(); // item cache for speed, but what if processing more than one XCAS file in a request?
    if (!isset($items[$id]))
      $items[$id] = $this->xpath->evaluate(sprintf("%s[@_id='%d']", $name, $id))->item(0);
    return $items[$id];
  }
  
  function items($id){
    return $this->xpath->evaluate(sprintf("uima.cas.FSArray[@_id='%d']/i", $id));
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
    $node = $this->item($id);
    return array($node->getAttribute('name'), $node->getAttribute('value'));
  }

  function parents($entity){
    $items = array();

    while ($entity->hasAttribute('_ref_entity')){
      $entity = $this->item($entity->getAttribute('_ref_entity'));
      $items[$entity->getAttribute('value')] = 1;
    }

    return $items;
  }

}