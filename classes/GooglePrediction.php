<?php

class GooglePrediction extends Google {
  public $doc = 'http://code.google.com/apis/predict/';
  
  public $cache = FALSE;

  function train($model){
    if (!$this->token)
      $this->authorise('xapi');
    $http = array('method' => 'POST', 'header' => "Content-Type: application/json\nAuthorization: GoogleLogin auth=" . $this->token, 'content' => '{data:{}}');
    return $this->get_data('https://www.googleapis.com/prediction/v1/training', array('data' => $model), 'json', $http);
  }
  
  function check_training($model){
    if (!$this->token)
      $this->authorise('xapi');
    $http = array('header' => "Authorization: GoogleLogin auth=" . $this->token);
    return $this->get_data('https://www.googleapis.com/prediction/v1/training/' . rawurlencode($model), array(), 'json', $http);
  }
  
  function predict($model, $text){
    $data = json_encode(
      array(
        'data' => array(
          'input' => array(
            'text' => array($text),
          ),
        ),
      )
    );
    
    $this->authorise();
    $http = array('header' => "Content-Type: application/json\nAuthorization: GoogleLogin auth=" . $this->token , 'content' => $data);
    return $this->get_data_curl('https://www.googleapis.com/prediction/v1/training/' . rawurlencode($model) . '/predict', array(), 'json', $http);
  }
}

