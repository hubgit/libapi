<?php

class Delicious extends API { 
  public $doc = 'http://delicious.com/';
  //public $def = 'DELICIOUS_AUTH'; // only for fetching a user's content
  
  public $results = array();
  public $total;
  public $tags = array();
   
  function get_bookmarks_for_item($uri){
    $this->get_data('http://badges.del.icio.us/feeds/json/url/data', array('url' => $uri));

    if (empty($json))
      return FALSE;

    $this->total = $this->data[0]->total_posts;
    $this->tags = $this->data[0]->top_tags;

    $dom = $this->get_data('http://feeds.delicious.com/v2/rss/url/' . md5($uri), array(), 'dom');

    foreach ($this->xpath->query('channel/item') as $node)
      $items[] = array(
        'user' => $node->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'creator')->item(0)->nodeValue,
        'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
        'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
        'tags' => $node->getElementsByTagName('category')->item(0)->nodeValue,
        );
  }

  # http://delicious.com/tag
  // TODO: is there a limit on the API?
  function content_by_tag($tag, $max = 1000000){
    require_once 'lib/css2xpath.inc.php';
          
    if ($this->output_dir)
      $this->output_dir = $this->get_output_dir($this->output_dir . '/' . preg_replace('/\W/', '_', $tag)); // FIXME: proper sanitising

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

    $page = 1;
    $n = 100;

    $count = 0;
    do{
      $this->get_data('http://delicious.com/tag/' . urlencode($tag), array(
        'setcount' => $n,
        'page' => $page,
        ), 'html', $http);

      foreach ($this->data->xpath($xpath['item']) as $item){
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

        if ($this->output_dir)
          file_put_contents(sprintf('%s/%s.js', $this->output_dir, base64_encode_file($data['user'] .  '*' . $data['uri'])), json_encode($data)); 
        else
          $this->results[] = $item;
      }

      $next = current($xml->xpath($xpath['next']));
      $page = 0;
      if (preg_match('/page=(\d+)$/', (string) $next['href'], $matches)){
        $page = $matches[1];
        sleep(1);
      }

      debug('Page ' . $page);
    } while ($page);
  }
  
  function content_by_user($args){
    extract($args);
          
    if (isset($output))
      $this->output_dir = $this->get_output_dir($output);
      
    $from = $this->get_latest($args, 0); // 0 = 1970-01-01T00:00:00Z

    $auth = explode(':', Config::get('DELICIOUS_AUTH'));

    $this->get_data(
      sprintf('https://%s:%s@api.del.icio.us/v1/posts/all', urlencode($auth[0]), urlencode($auth[1])), 
      array('fromdt' => gmdate("Y-m-d\TH:i:s\Z", $from)),
      'xml');
      
    if (!isset($xml->post))
      return FALSE;

    foreach ($this->data->post as $item){
      if ($this->output_dir)
        file_put_contents(sprintf('%s/%s.xml', $this->output_dir, preg_replace('/\W/i', '', (string) $item['hash'])), $item->asXML()); 
      else
        $this->results[] = $item;
    }

    if ($this->output_dir)
      file_put_contents($this->output_dir . '/latest', strtotime((string) $xml['update']));
  }
}
