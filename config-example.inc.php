<?php

/* API keys */

//define('GOOGLE_MAPS_API_KEY', 'YOUR_API_KEY');
//define('MULTIMAP_API_KEY', 'YOUR_API_KEY');
//define('YAHOO_API_KEY', 'YOUR_API_KEY');
//define('SCOPUS_API_KEY', 'YOUR_API_KEY');
//define('OPENCALAIS_API_KEY', 'YOUR_API_KEY');

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