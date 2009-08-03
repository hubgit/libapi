<?php

require '../main.inc.php';

$q = array(
  'openurl' => array(
    'url_ver' => 'Z39.88-2004',
    'rft_val_fmt' => 'info:ofi/fmt:kev:mtx:journal',
    'rft.atitle' => 'Isolation of a common receptor for coxsackie B',
    'rft.jtitle' => 'Science',
    'rft.aulast' => 'Bergelson',
    'rft.auinit' => 'J',
    'rft.date' => '1997',
    'rft.volume' => '275',
    'rft.spage' => '1320',
    'rft.epage' => '1323',
    )
  );

$api = new API('metadata', 'crossref');
$responses = $api->run($q);
debug($responses);
