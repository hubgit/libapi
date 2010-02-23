<?php

class CrossRef extends API {
  public $doc = 'http://www.crossref.org/citedby.html';
  public $def = 'CROSSREF_AUTH'; // http://www.crossref.org/requestaccount/

  function citedby($args){
    $this->validate($args, 'doi'); extract($args);

    $auth = explode(':', Config::get('CROSSREF_AUTH'));

    $xml = $this->get_data('http://doi.crossref.org/servlet/getForwardLinks', array(
      'doi' => $doi,
      'usr' => $auth[0],
      'pwd' => $auth[1],
      ), 'xml');

    //debug($xml);

    if (!is_object($xml))
      return FALSE;

    $items = array();
    foreach ($xml->query_result->body->forward_link as $item){  
      $cite = $item->journal_cite;
      //debug($item);  
      $items[(string) $cite->doi] = array(
        'citedby' => (int) $cite['fl_count'],
        'year' => (int) $cite->year,
        'xml' => $cite,
        );
    }
    
    return array($items, array('total' => count($items)));
  }

  function metadata($args){
    if (!$args['uri'] && $args['doi'])
      $args['uri'] = 'http://dx.doi.org/' . $args['doi'];
    
    extract($args);
    
    if (!$uri && !$openurl)
      trigger_error('URI or OpenURL needed', E_USER_ERROR);

    $params = array(
      'noredirect' => 'true',
      'format' => 'unixref',
      'pid' => Config::get('CROSSREF_AUTH'),
      );

    if ($uri)
      $params['id'] = $uri;

    if ($openurl)
      $params = array_merge($params, $openurl);

    $xml = $this->get_data('http://www.crossref.org/openurl/', $params, 'xml');
    //debug($xml);

    if (!is_object($xml) || empty($xml->doi_record))
      return FALSE;

    $record = $xml->doi_record->crossref->journal;

    $article = $record->journal_article;
    $journal = $record->journal_metadata;
    $issue = $record->journal_issue;

    if (!is_object($article))
      return FALSE;

    return $record;
  }
   
}