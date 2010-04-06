<?php

class Amplify extends API {
  public $doc = 'http://community.openamplify.com/content/apidocs.aspx';
  public $def = 'AMPLIFY';
      
  function analyse($text){
    $this->get_data('http://portaltnx.openamplify.com/AmplifyWeb/AmplifyThis', array(
      'apiKey' => Config::get('AMPLIFY'),
      'outputFormat' => 'xml',
      'inputText' => $text,
      ), 'dom');

    $result = array();
    foreach ($this->xpath->query("//TopicResult") as $node)
      $result['Topic'][$this->xpath->query("Topic/Name", $node)->item(0)->nodeValue] = $this->xpath->query("Polarity/Mean/Name", $node)->item(0)->nodeValue;

    foreach ($this->xpath->query("//ActionResult") as $node)
      $result['Action'][$this->xpath->query("Action/Name", $node)->item(0)->nodeValue] = $this->xpath->query("ActionType/Result/Name", $node)->item(0)->nodeValue;
          
    foreach ($this->xpath->query("//Demographics") as $node)
      $result['Demographics'] = array(
        'Age' => $this->xpath->query("Age/Name", $node)->item(0)->nodeValue,
        'Gender' => $this->xpath->query("Gender/Name", $node)->item(0)->nodeValue,
        'Education' => $this->xpath->query("Education/Name", $node)->item(0)->nodeValue,
        );
        
    foreach ($this->xpath->query("//Styles") as $node)
      $result['Styles'] = array(
        'Slang' => $this->xpath->query("Slang/Name", $node)->item(0)->nodeValue,
        'Flamboyance' => $this->xpath->query("Flamboyance/Name", $node)->item(0)->nodeValue,
        );
    
    return $result;
  }  
}