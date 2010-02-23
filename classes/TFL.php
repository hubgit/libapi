<?php

class TFL extends API {
  //public $doc = '';
  //public $def = '';

  function stop($args){
    $this->validate($args, 'stopcode'); extract($args);   
    return $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/StopInfo.aspx', array('stopcode' => $stopcode));  
  }
  
  function route($args){
    $this->validate($args, array('route', 'run')); extract($args);
    return $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/FullRoute.aspx', array('route' => $route, 'run' => $run));  
  }
  
  function route_search($args){
    $this->validate($args, array('latitude', 'longitude')); extract($args);
    return $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/Search.aspx', array('Lat' => $latitude, 'Lng' => $longitude));  
  }

  function timetable($args){
    $this->validate($args, array('route', 'stopcode')); extract($args);

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

