<?php

/* API keys */

//define('GOOGLE_MAPS_KEY', 'YOUR_API_KEY');
//define('MULTIMAP_KEY', 'YOUR_API_KEY');
//define('YAHOO_KEY', 'YOUR_API_KEY');
//define('SCOPUS_KEY', 'YOUR_API_KEY');
//define('OPENCALAIS_KEY', 'YOUR_API_KEY');

//define('BLOGLINES_USER', '');
//define('BLOGLINES_KEY', '');

/* active sources */

global $enabled;
$enabled['geocode'] = array(
  'google',
  'yahoo',
  //'multimap',
  //'geonames',
);

$enabled['citedby'] = array(
  'scopus',
  //'thomson', 
  //'bloglines',
  //'citeulike',
  );

$enabled['content'] = array(
  'wikipedia',
  );
  
$enabled['entities'] = array(
  'opencalais',
  'placemaker',
  'whatizit',
  );

$enabled['entities_pmid'] = array(
  'gopubmed',
  //'biocreative',
  );