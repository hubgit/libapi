<?php

require '../main.inc.php';

/*
$q = 'anatomy';
$api = new API('search', 'guardian');
$responses = $api->run($q);
*/

/*
$location = 'san francisco';
$json = yql("SELECT woeid FROM geo.places WHERE text = '%s' LIMIT 1", $location);
$woeid = $json->query->results->place->woeid;
debug($woeid);
*/

$woeid = 2487956;

$api = new API('search', 'flickr');

$data = $api->run(array(
  'woe_id' => $woeid, 
  'has_geo' => TRUE,
  'sort' => 'interestingness-desc',
  'min_taken_date' => date('Y-m-d H:i:s', time() - 60*60*24*365), // 1 year ago
  ));

debug($data);
