<?php

# http://www.flickr.com/services/api/flickr.photos.getInfo.htm

return defined('FLICKR_KEY') && defined('FLICKR_SECRET');

function content_flickr_user($q){
  if (!$user = $q['user'])
    return FALSE;
    
  if (isset($q['output']))
    $output_folder = output_folder($q['output']);
    
  if (isset($q['from']))
    $from = $q['from'];
  else if (file_exists($output_folder . '/latest'))
    $from = filemtime($output_folder . '/latest');
  else
    $from = 0; // 1970-01-01T00:00:00Z
   
  $n = 500;
  $page = 1; // pages start at 1
  
  $flickr = new Flickr();
  $api = new API('metadata', 'flickr');
  
  $items = array();
   
  do {
    $params = array(
      'user_id' => $user,
      'min_upload_date' => $from,
      'per_page' => $n,
      'page' => $page,
      );

   $data = $flickr->api('flickr.photos.search', $params);
  
   //debug($data);
    
    if (!is_array($data) || $data['stat'] != 'ok' || empty($data['photos']['photo']))
      return FALSE;
    
    foreach ($data['photos']['photo'] as $photo){
      if ($output_folder){
        $id = preg_replace('/\D/', '', $photo['id']); // can't use %d as too big
        
        $out = sprintf('%s/%s.js', $output_folder, $id);
        if (file_exists($out))
          continue;
          
        $result = $api->run($photo);
        $item = $result['flickr'];
        //debug($item);
        
        $format = $item['originalformat'] ? $item['originalformat'] : 'jpg';
        $secret = $item['originalsecret'] ? $item['originalsecret'] : $item['secret'];      
        $suffix = $item['originalsecret'] ? '_o' : '';
        
        $img = sprintf('http://farm%d.static.flickr.com/%d/%s_%s%s.%s', $item['farm'], $item['server'], $item['id'], $secret, $suffix, $format);
        debug($img);
        
        file_put_contents(sprintf('%s/%s%s.jpg', $output_folder, $id, $suffix), file_get_contents($img));
        file_put_contents($out, json_encode($item));
      }
      else
        $items[] = $photo;
    }
  
    sleep(1);
    
  } while ($page++ <= $data['photos']['pages']);
  
  //if ($output_folder)
    //file_put_contents($output_folder . '/latest', strtotime((string) $xml['update']));

  return $items;
}
