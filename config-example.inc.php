<?php

/* API keys */

//define('GOOGLE_MAPS_KEY', 'YOUR_KEY');
//define('MULTIMAP_KEY', 'YOUR_KEY');
//define('YAHOO_KEY', 'YOUR_KEY');
//define('SCOPUS_KEY', 'YOUR_KEY');
//define('OPENCALAIS_KEY', 'YOUR_KEY');

/* active sources */

global $enabled;
$enabled['geocode'] = array(
  'google',
  'yahoo',
  //'multimap',
  //'geonames',
);

$enabled['citedby'] = array(
  //'scopus',
  //'thomson', 
  );

$enabled['content'] = array(
  //'wikipedia',
  );
  
$enabled['entities'] = array(
  'opencalais',
  'placemaker',
  );