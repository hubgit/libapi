<?php

class CrossRef extends API {
  public $doc = 'http://www.crossref.org/citedby.html';
  public $def = 'CROSSREF_AUTH'; // http://www.crossref.org/requestaccount/
  
  function citedby($doi){
    $auth = explode(':', Config::get('CROSSREF_AUTH'));

    $this->get_data('http://doi.crossref.org/servlet/getForwardLinks', array(
      'doi' => $doi,
      'usr' => $auth[0],
      'pwd' => $auth[1],
      ), 'xml');

    $items = array();
    foreach ($this->data->query_result->body->forward_link as $item){  
      $cite = $item->journal_cite;
      $this->results[(string) $cite->doi] = array(
        'citedby' => (int) $cite['fl_count'],
        'year' => (int) $cite->year,
        'xml' => $cite,
        );
    }
    
    $this->total = count($this->results);
  }

  function metadata($uri, $data = array()){
    if (!$uri && $data['doi'])
      $uri = 'http://dx.doi.org/' . $data['doi'];
        
    if (!$uri && !$data['openurl'])
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

    $this->get_data('http://www.crossref.org/openurl/', $params, 'xml');

    if (empty($this->data->doi_record))
      return FALSE;

    $record = $this->data->doi_record->crossref->journal;

    $article = $record->journal_article;
    $journal = $record->journal_metadata;
    $issue = $record->journal_issue;

    if (!is_object($article))
      return FALSE;

    return $record;
  }
   
}