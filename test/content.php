<?php

require '../main.inc.php';

$q = array(
  //'title' => 'Google',
  
  //'nytimes-facet' => 'SCIENCE AND TECHNOLOGY',
  
  //'guardian-filter' => '/science',
  //'output' => '../data/guardian/articles',
  
  //'feed-url' => 'http://newsrss.bbc.co.uk/rss/newsonline_world_edition/science/nature/rss.xml',
  //'feed-url' => 'http://feeds.feedburner.com/bbcnewssciencenaturefullfeed',
  //'output' => '../data/bbc-news/full',
  
  'tag' => 'science2.0',
  'output' => '../data/delicious/tag/science2.0',
  );

//$api = new API('content');
$api = new API('content', 'delicious_tag');
$responses = $api->all($q);
debug($responses);
