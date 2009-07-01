<?php

# http://delicious.com/tag

require 'lib/css2xpath.inc.php';

function content_delicious_tag($q){
  if (!$query = $q['tag'])
    return FALSE;
    
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
  
  $css2xpath = new CSS2XPath();
      
  $xpath = array_map(array($css2xpath, 'transform'), array(
    'item' => '#bookmarklist .post .bookmark',
    'link' => 'a.taggedlink',
    'user' => 'a.user span',
    'next' => '#pagination .next',
    'count' => '.delNavCount',
    'tag' => 'a[rel=tag] span',
    'date' => '.dateGroup span',
    ));
    
  $items = array();
  $page = 1;
  $n = 100;
  
  do{
    $xml = get_data('http://delicious.com/tag/' . urlencode($query), array(
      'setcount' => $n,
      'page' => $page,
      ), 'html', $http);

    //debug($xml);
    
    if (!is_object($xml))
      break;
    
    foreach ($xml->xpath($xpath['item']) as $item){
      $link = current($item->xpath($xpath['link']));
      
      $result = $item->xpath($xpath['date']);
      if (!empty($result))
        $date = strtotime((string) $result[0]['title']);
      if (!$date)
        break(2);
      
      $data = array(
        'uri' => (string) $link['href'],
        'title' => (string) $link,
        'user' => (string) current($item->xpath($xpath['user'])),
        'count' => (int) current($item->xpath($xpath['count'])),
        'date' => $date,
      );
            
      if (!$data['uri'])
        break(2);
      
      $tags = array();
      foreach ($item->xpath($xpath['tag']) as $tag)
        $data['tags'][] = (string) $tag;
        
      debug($data);   
            
      if ($output_folder)
        file_put_contents(sprintf('%s/%s.js', $output_folder, base64_encode_file($data['user'] .  '*' . $data['uri'])), json_encode($data)); 
      else
        $items[] = $item;
    }

    $next = current($xml->xpath($xpath['next']));
    $page = 0;
    if (preg_match('/page=(\d+)$/', (string) $next['href'], $matches)){
      $page = $matches[1];
      sleep(1);
    }

    debug('Page ' . $page);
  } while ($page);
  
  return $items;
}
