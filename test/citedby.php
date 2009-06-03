<?php

require '../main.inc.php';

$q = array(
  'doi' => '10.1038/nature05432',
  //'doi' => '10.1006/mpev.2001.0963',
  //'pmid' => 11476639,
  );

$api = new API('citedby');
$responses = $api->all($q);
debug($responses);
