<?php

class Whatizit extends API {
  public $doc = 'http://www.ebi.ac.uk/webservices/whatizit/';
  public $wsdl = 'http://www.ebi.ac.uk/webservices/whatizit/ws?wsdl';

  function annotate($text, $plan = 'whatizitSwissprot'){
    $timeout = ini_get('default_socket_timeout');
    ini_set('default_socket_timeout', 3600); // 1 hour

    $params = array('text' => $text, 'pipelineName' => $plan, 'convertToHtml' => FALSE);
    $this->soap($this->wsdl, 'contact', $params);

    ini_set('default_socket_timeout', $timeout);

    $this->response = $this->data->return;
    $this->api->formatOutput = FALSE;
    $this->data = $this->format_data('dom');
    $this->xpath->registerNamespace('ebi', 'http://www.ebi.ac.uk/z');

    $this->textnodes = $this->xpath->query("//text");

    $items = array();
    $nodes = $this->xpath->query("//ebi:uniprot");
    foreach ($nodes as $node){
      //$position = $this->findPositionForNode($node);
      if (!isset($items[$node->textContent]))
        $items[$node->textContent] = explode(',', $node->getAttribute('ids'));
    }

    // TODO: is this sorting necessary?
    $titles = array();
    foreach (array_keys($items) as $title)
      $titles[$title] = mb_strlen($title);
    arsort($titles);    

    $textLower = strtolower($text);
    
    $this->annotations = array();
    foreach ($titles as $title => $length){
      $positions = raw_preg_match_all($textLower, strtolower($title));
      foreach ($positions as $position)
        $this->annotations[] = array(
          'start' => $position,
          'end' => $position + $length,
          'type' => 'protein',
          'text' => $title,
          //'data' => array('uniprot:id' => $items[$title]),
          );
    }
	return $this->annotations;
  }
}