<?php

class TFL extends API {
  //public $doc = '';
  //public $def = '';

  function stop_data($q){
    if (!$stopcode = $q['stopcode'])
      return FALSE;

    $json = $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/StopInfo.aspx', array('stopcode' => $stopcode));  
    //debug($json);

    if (!is_object($json))
      return FALSE;

    return $json;
  }

  function stop_route_timetable($q){
    if (!$route = $q['route'])
      return FALSE;

    if (!$stopcode = $q['stopcode'])
      return FALSE;

    $xml = $this->get_data(sprintf('http://www.tfl.gov.uk/tfl/syndication/feeds/html/timetables/buses/%s_%s.htm', $route, $stopcode), NULL, 'html');
    // debug($xml);
    
    $css2xpath = new CSS2XPath;
    $xpath = $css2xpath->transform('.TT_DayType');
    $nodes = $xml->xpath($xpath);

    $data = array();
    foreach ($nodes as $node){
      if ($node['class'] == 'TT_DayType c5'){
        $day = (string) $node->tr->td->p;
        continue;
      }
      if (!$day)
        continue;

      $headers = $node->tr[0]->th;
      $items = $node->tr[1]->td;
      for ($i = 0; $i < count($headers); $i++){
        $header = preg_replace('/\s+/', ' ', (string) $headers[$i]);
        $item = isset($items[$i]->table) ? innerXML($items[$i]->table->tr->td) : outerXML($items[$i]);
        $data[$day][$header] = $item;
      }
    }

    return $data;    
  }
}

