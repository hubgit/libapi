<?php

require '../main.inc.php';

$q = array(
  //'title' => 'Google',
  
  //'nytimes-facet' => 'SCIENCE AND TECHNOLOGY',
  
  //'guardian-filter' => '/science',
  //'output' => '/guardian/articles',
  
  //'feed-url' => 'http://newsrss.bbc.co.uk/rss/newsonline_world_edition/science/nature/rss.xml',
  //'feed-url' => 'http://feeds.feedburner.com/bbcnewssciencenaturefullfeed',
  //'output' => '/bbc-news/full',
  
  //'tag' => 'science2.0',
  //'output' => '/delicious/tag/science2.0',
    
  'npr-topic' => '1007',
  'output' => '/npr/articles',
  );

//$api = new API('content');
$api = new API('content', 'npr');
$responses = $api->run($q);
debug($responses);
