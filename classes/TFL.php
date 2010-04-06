<?php

class TFL extends API {
  //public $doc = '';
  //public $def = '';
  
  public $results = array();

  function stop($stopcode){
    $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/StopInfo.aspx', array('stopcode' => $stopcode));  
  }
  
  function route($route, $run){
    $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/FullRoute.aspx', array('route' => $route, 'run' => $run));  
  }
  
  function route_search($latitude, $longitude){
    $this->get_data('http://www.tfl.gov.uk/tfl/gettingaround/maps/buses/tfl-bus-map/dotnet/Search.aspx', array('Lat' => $latitude, 'Lng' => $longitude));  
  }

  function timetable($route, $stopcode){
    $this->get_data(sprintf('http://www.tfl.gov.uk/tfl/syndication/feeds/html/timetables/buses/%s_%s.htm', $route, $stopcode), NULL, 'html-dom');
    
    $css2xpath = new CSS2XPath;
    $xpath = $css2xpath->transform('.TT_DayType');
    $nodes = $this->xpath->query($xpath);

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
        $this->results[$day][$header] = $item;
      }
    }
  }
}

