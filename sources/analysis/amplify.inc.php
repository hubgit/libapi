<?php

#

return defined('AMPLIFY_KEY');

function analysis_amplify($q){
  if (!$text = $q['text'])
    return FALSE;
    
  $xml = get_data('http://portaltnx.openamplify.com/AmplifyWeb/AmplifyThis', array(
    'apiKey' => AMPLIFY_KEY,
    'outputFormat' => 'xml',
    'inputText' => $text,
    ), 'xml');
  
  debug($xml);
  
  if (!is_object($xml))
    return array();
    
  $entities = array(
    'Topic' => xpath_items($xml, "//Topic/Name"),
    'Action' => xpath_items($xml,"//Action/Name"),     
    );
  
  foreach ($xml->xpath('//Demographics') as $item) {
    $entities['Demographics'][] = array(
      'Age' => xpath_item($item, "Age/Name"),
      'Gender' => xpath_item($item, "Gender/Name"),
      'Education' => xpath_item($item, "Education/Name"),
      );
  }

  foreach ($xml->xpath('//Styles') as $item) {
    $entities['Styles'][] = array(
      'Slang' => xpath_item($item, "Slang/Name"),
      'Flamboyance' => xpath_item($item, "Flamboyance/Name"),
      );
  }
  
  return array($entities);
}

