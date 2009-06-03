<?php

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
  //'whatizit',
  );

$enabled['entities_pmid'] = array(
  'gopubmed',
  //'biocreative',
  );
  
$enabled['search'] = array(
  'boss',
  'google',
  );
