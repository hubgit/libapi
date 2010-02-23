<?php

class Amplify extends API {
  public $doc = 'http://community.openamplify.com/content/apidocs.aspx';
  public $def = 'AMPLIFY';
  
  function analyse($args){
    $this->validate($args, 'text'); extract($args);

    $dom = $this->get_data('http://portaltnx.openamplify.com/AmplifyWeb/AmplifyThis', array(
      'apiKey' => Config::get('AMPLIFY'),
      'outputFormat' => 'xml',
      'inputText' => $text,
      ), 'dom');


    if (!is_object($dom))
      return FALSE;

    $xpath = new DOMXPath($dom);

    $result = array();
    foreach ($xpath->query("//TopicResult") as $node)
      $result['Topic'][$xpath->query("Topic/Name", $node)->item(0)->nodeValue] = $xpath->query("Polarity/Mean/Name", $node)->item(0)->nodeValue;

    foreach ($xpath->query("//ActionResult") as $node)
      $result['Action'][$xpath->query("Action/Name", $node)->item(0)->nodeValue] = $xpath->query("ActionType/Result/Name", $node)->item(0)->nodeValue;
          
    foreach ($xpath->query("//Demographics") as $node)
      $result['Demographics'] = array(
        'Age' => $xpath->query("Age/Name", $node)->item(0)->nodeValue,
        'Gender' => $xpath->query("Gender/Name", $node)->item(0)->nodeValue,
        'Education' => $xpath->query("Education/Name", $node)->item(0)->nodeValue,
        );
        
    foreach ($xpath->query("//Styles") as $node)
      $result['Styles'] = array(
        'Slang' => $xpath->query("Slang/Name", $node)->item(0)->nodeValue,
        'Flamboyance' => $xpath->query("Flamboyance/Name", $node)->item(0)->nodeValue,
        );

    return $result;
  }  
}