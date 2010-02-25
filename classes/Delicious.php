<?php

class Delicious extends API { 
  public $doc = 'http://delicious.com/';
  //public $def = 'DELICIOUS_AUTH'; // only for fetching a user's content
   
  function get_bookmarks_for_item($args){
    $this->validate($args, 'uri'); extract($args);

    $json = $this->get_data('http://badges.del.icio.us/feeds/json/url/data', array('url' => $uri));
    //debug($json);
    
    if (!is_array($json) || empty($json))
      return FALSE;

    $meta = array('total' => $json[0]->total_posts, 'tags' => $json[0]->top_tags);
    
    $dom = $this->get_data('http://feeds.delicious.com/v2/rss/url/' . md5($uri), array(), 'dom');
    //debug(simplexml_import_dom($dom));
  
    $items = array();
    if (is_object($dom)){
      $xpath = new DOMXPath($dom);
      foreach ($xpath->query('channel/item') as $node)
        $items[] = array(
          'user' => $node->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'creator')->item(0)->nodeValue,
          'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
          'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
          'tags' => $node->getElementsByTagName('category')->item(0)->nodeValue,
          );
    }

    return array($items, $meta);
  }
  
  # http://delicious.com/tag
  function content_by_tag($args){
    require_once 'lib/css2xpath.inc.php';
    
    $this->validate($args, 'tag', array('max' => 1000000)); extract($args); // TODO: is there a limit on the API?
      
    if ($output)
      $this->output_dir = $this->get_output_dir($output . '/' . preg_replace('/\W/', '_', $tag)); // FIXME: proper sanitising

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

    $count = 0;
    do{
      $xml = $this->get_data('http://delicious.com/tag/' . urlencode($tag), array(
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

        if ($this->output_dir)
          file_put_contents(sprintf('%s/%s.js', $this->output_dir, base64_encode_file($data['user'] .  '*' . $data['uri'])), json_encode($data)); 
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
  
  function content_by_user($args){
    extract($args);
          
    if (isset($output))
      $this->output_dir = $this->get_output_dir($output);
      
    $from = $this->get_latest($args, 0); // 0 = 1970-01-01T00:00:00Z

    $auth = explode(':', Config::get('DELICIOUS_AUTH'));

    $xml = $this->get_data(
      sprintf('https://%s:%s@api.del.icio.us/v1/posts/all', urlencode($auth[0]), urlencode($auth[1])), 
      array('fromdt' => gmdate("Y-m-d\TH:i:s\Z", $from)),
      'xml');

    //debug($xml);

    if (!is_object($xml) || !isset($xml->post))
      return FALSE;

    $items = array();

    foreach ($xml->post as $item){
      if ($this->output_dir)
        file_put_contents(sprintf('%s/%s.xml', $this->output_dir, preg_replace('/\W/i', '', (string) $item['hash'])), $item->asXML()); 
      else
        $items[] = $item;
    }

    if ($this->output_dir)
      file_put_contents($this->output_dir . '/latest', strtotime((string) $xml['update']));

    return $items;
  }
}
