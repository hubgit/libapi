<?php

class PubMed extends API {
  public $doc = 'http://www.ncbi.nlm.nih.gov/entrez/query/static/eutils_help.html';
  
  public $count;
  public $webenv;
  public $querykey;
 
  public $cache = TRUE;
  
  function search_soap($q, $params = array()){
    unset($this->count, $this->webenv, $this->querykey);
    
    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'usehistory' => 'y',
      'retmax' => 1,
      'term' => $q,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );
      
    $client = new SoapClient('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/eutils.wsdl'); 
    $this->data = $client->run_eSearch(array_merge($default, $params));
    
    $this->count = $this->data->Count;
    $this->webenv = $this->data->WebEnv;
    $this->querykey = $this->data->QueryKey;
  }
  
  function search($q, $params = array()){
    unset($this->count, $this->webenv, $this->querykey);

    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'usehistory' => 'y',
      'retmax' => 1,
      'term' => $q,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );

    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array_merge($default, $params), 'dom');

    $this->count = $this->data->getElementsByTagName("Count")->item(0)->nodeValue;
    $this->webenv = $this->data->getElementsByTagName("WebEnv")->item(0)->nodeValue;
    $this->querykey = $this->data->getElementsByTagName("QueryKey")->item(0)->nodeValue;
  }

  function fetch($ids = NULL, $params = array()){
    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );
      
    if (!empty($ids)){
      $default['id'] = implode(',', is_array($ids) ? $ids : array($ids));
    }
    else if ($this->webenv){
      $default['query_key'] = $this->querykey;
      $default['WebEnv'] = $this->webenv;
    }

    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array_merge($default, $params), 'dom');
  }
  
  function related($pmid, $params = array()){
    if (!is_array($pmid))
      $pmid = array($pmid);
    
    $default = array(
       'db' => 'pubmed',
       'dbfrom' => 'pubmed',
       'id' => implode(',', $pmid),
       'retmode' => 'xml',
       'tool' => Config::get('EUTILS_TOOL'),
       'email' => Config::get('EUTILS_EMAIL'),
     );
     
    $dom = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi', array_merge($default, $params), 'dom');
    if (!is_object($dom))
      return FALSE;
      
    $xpath = new DOMXPath($dom);      
      
    $items = array();
    foreach ($xpath->query("LinkSet/LinkSetDb/Link") as $link)
      $items[] = $link->getElementsByTagName("Id")->item(0)->nodeValue;
    
    $this->count = count($items);
    return $items;
  }
  
  function fulltext($pmid){
    $params = array(
      'db' => 'pubmed', 
      'retmode' => 'xml', 
      'id' => $pmid,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
    );
    
    $dom = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', $params, 'dom');
    if (!is_object($dom))
      return FALSE;
      
    $xpath = new DOMXPath($dom);  
    
    $nodes = $xpath->query("DocSum/Item[@Name='DOI']");
    return $node->length ? 'http://dx.doi.org/' . $nodes->item(0)->nodeValue : 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?cmd=prlinks&dbfrom=pubmed&retmode=ref&id=' . $pmid;
  }

  function content($term, $max, $from){
    /*
    $args = filter_var_array($args, array(
      'max' => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => FILTER_REQUIRE_SCALAR,
        'options' => array('default' => 10000000), // TODO: is there a limit?
       ),
    ));
    */
  
    $from = $this->get_latest(array('from' => $from), 0); // 0 = 1970-01-01T00:00:00Z

    $to = date('Y/m/d', time() + 60*60*24*365*10); // 10 years in future

    $n = min($max, 500);
    $count = 0;

    foreach (array('edat', 'mdat') as $datetype){ // edat = date added to entrez (pdat = published date), mdat = date modified
      $start = 0;

      $params = array(
        'mindate' => $from,
        'maxdate' => $to,
        'datetype' => $datetype,
        );

      $this->search($term, $params);

      do {
        $params = array(
          'retmax' => $n,
          'retstart' => $start,
          //'sort' => 'pub+date',
          );
     
        $this->fetch(NULL, $params);
    
        foreach ($this->xpath->query("PubmedArticle") as $article){
          $medline = $this->xpath->query("MedlineCitation", $article)->item(0);          
          $id = $this->xpath->query("PMID", $medline)->item(0)->nodeValue;
          debug($id);
          $status = $medline->getAttribute('Status');

          if ($this->output_dir)
            $article->save(sprintf('%s/%d.xml', $this->output_dir, $id));
          else
            $this->results[$id] = $article;
        }
  
        sleep(1);
    
        $start += $n;
      } while ($start < $pubmed->count);
    }
    
    if ($this->output_dir)
      file_put_contents($this->output_dir . '/latest', date('Y/m/d'));
  }
  
  // fetch an individual item from PubMed by DOI or PMID
  // TODO: clean up
  function metadata($args){
    extract($args);
    if (!$pmid && $doi){
      $dom = $this->get_cached_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array(
        'db' => 'pubmed',
        'retmode' => 'xml',
        'retmax' => 1,
        'usehistory' => 'n',
        'term' => $doi . '[DOI]',
        'tool' => Config::get('EUTILS_TOOL'),
        'email' => Config::get('EUTILS_EMAIL'),
        ), 'dom');
      
      
      if ((int) $dom->getElementsByTagName('Count')->nodeValue > 0)  
        $pmid = (int) $dom->getElementsByTagName('IdList')->item(0)->getElementsByTagName('Id')->item(0)->nodeValue;
    }

    if (!$pmid)
      return FALSE;

    $dom = $this->get_cached_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'id' => $pmid,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      ), 'dom');


    if (!is_object($dom))
      return FALSE;

    $article = $dom->getElementsByTagName('PubmedArticle')->item(0)->getElementsByTagName('MedlineCitation')->item(0)->getElementsByTagName('Article')->item(0);
    $xpath = new DOMXpath($article);

    $doi = $xpath->query("//ArticleIdList/ArticleId[@IdType='doi']")->item(0)->nodeValue;
    $pmid = (int) $xpath->query("//ArticleIdList/ArticleId[@IdType='pubmed']")->item(0)->nodeValue;

    $authors = array();
    foreach ($xpath->query("AuthorList/Author") as $author)
      $authors[] = implode(' ', array($author->getElementsByTagName('Initials')->item(0)->nodeValue, $author->getElementsByTagName('LastName')->item(0)->nodeValue));

    return array(
      'pmid' => $xpath->query("//ArticleIdList/ArticleId[@IdType='pubmed']")->item(0)->nodeValue,
      'title' => $xpath->query("ArticleTitle")->item(0)->textContent,
      'journal' => $xpath->query("Journal/Title")->item(0)->textContent,
      'year' => $xpath->query("Journal/JournalIssue/PubDate/Year")->item(0)->nodeValue,
      'abstract' => $xpath->query("Abstract/AbstractText")->item(0)->textContent,
      'doi' => $xpath->query("//ArticleIdList/ArticleId[@IdType='doi']")->item(0)->nodeValue,
      'authors' => $authors,
      'raw' => $article,
      );
  }
}
