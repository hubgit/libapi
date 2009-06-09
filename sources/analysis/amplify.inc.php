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
  
  //debug($xml);
  
  if (!is_object($xml))
    return FALSE;
    
  $entities = array();
    
  foreach ($xml->xpath('//TopicResult') as $item) {
    $name = xpath_item($item, "Topic/Name");
    $entities['Topic'][$name] = xpath_item($item, "Polarity/Mean/Name");
  }
  
  foreach ($xml->xpath('//ActionResult') as $item) {
    $name = xpath_item($item, "Action/Name");
    $entities['Action'][$name] = xpath_item($item, "ActionType/Result/Name");
  }
  
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

