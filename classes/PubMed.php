<?php

class PubMed extends API {
  public $doc = 'http://www.ncbi.nlm.nih.gov/entrez/query/static/eutils_help.html';
  
  public $count;
  public $webenv;
  public $querykey;
  
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

    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array_merge($default, $params), 'xml');

    $this->count = (int) $this->data->Count;
    $this->webenv = (string) $this->data->WebEnv;
    $this->querykey = (int) $this->data->QueryKey;
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

    return $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array_merge($default, $params), 'xml');
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
     
    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi', array_merge($default, $params), 'xml');
    //debug($xml);
    if (!is_object($xml))
      return FALSE;
      
      
     $items = array();
     foreach ($xml->LinkSet->LinkSetDb->Link as $link)
       $items[] = (string) $link->Id;
    
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
    
    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', $params, 'xml');
    if (!is_object($xml))
      return FALSE;
    
    $nodes = $xml->xpath("DocSum/Item[@Name='DOI']");
    return empty($nodes) ? 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?cmd=prlinks&dbfrom=pubmed&retmode=ref&id=' . $pmid : 'http://dx.doi.org/' . (string) $nodes[0];
  }

  function content($args){
    /*
    $args = filter_var_array($args, array(
      'max' => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => FILTER_REQUIRE_SCALAR,
        'options' => array('default' => 10000000), // TODO: is there a limit?
       ),
    ));
    */
    
    $this->validate($args, 'term', array('max' => 10000000)); extract($args); // TODO: is there a limit?
    
    if ($output)
      $this->output_dir = $this->get_output_dir($output);
  
    $from = $this->get_latest($args, 0); // 0 = 1970-01-01T00:00:00Z

    $to = date('Y/m/d', time() + 60*60*24*365*10); // 10 years in future

    $n = min($max, 500);
    $items = array();
    $count = 0;

    foreach (array('edat', 'mdat') as $datetype){ // edat = date added to entrez (pdat = published date), mdat = date modified
      $start = 0;

      $params = array(
        'mindate' => $from,
        'maxdate' => $to,
        'datetype' => $datetype,
        );

      $result = $this->search($term, $params);
      if (!$result)
        return FALSE;

      do {
        $params = array(
          'retmax' => $n,
          'retstart' => $start,
          //'sort' => 'pub+date',
          );
     
        $xml = $this->fetch(NULL, $params);
    
        if (!is_object($xml))
          return FALSE;
      
        //debug($xml);
    
        foreach ($xml->PubmedArticle as $article){           
          $id = (int) $article->MedlineCitation->PMID;
          $status = (string) $article->MedlineCitation['Status'];
          //if ($status == 'In-Data-Review') // FIXME
            //continue;
          
          if ($this->output_dir){
            $out = sprintf('%s/%d.xml', $this->output_dir, $id); // id = integer
            file_put_contents($out, $article->asXML());
          }
          else
            $items[$id] = $article;
        }
  
        sleep(1);
    
        $start += $n;
      } while ($start < $pubmed->count);
    }

    file_put_contents($this->output_dir . '/latest', date('Y/m/d'));
  
    return $items;
  }
  
  // fetch an individual item from PubMed by DOI or PMID
  // TODO: clean up
  function metadata($args){
    extract($args);
    if (!$pmid && $doi){
      $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array(
        'db' => 'pubmed',
        'retmode' => 'xml',
        'retmax' => 1,
        'usehistory' => 'n',
        'term' => $doi . '[DOI]',
        'tool' => Config::get('EUTILS_TOOL'),
        'email' => Config::get('EUTILS_EMAIL'),
        ), 'xml');

      debug($xml);

      if ((int) $xml->Count > 0)  
        $pmid = (int) $xml->IdList->Id[0];
    }

    if (!$pmid)
      return FALSE;

    $xml = $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'id' => $pmid,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      ), 'xml');

    //debug($xml);

    if (!is_object($xml))
      return FALSE;

    $article = $xml->PubmedArticle[0]->MedlineCitation->Article;

    $doi = (string) current($xml->xpath("//ArticleIdList/ArticleId[@IdType='doi']"));
    $pmid = (int) current($xml->xpath("//ArticleIdList/ArticleId[@IdType='pubmed']"));

    $authors = array();
    foreach ($article->AuthorList->Author as $author)
      $authors[] = implode(' ', array((string) $author->Initials, (string) $author->LastName));

    return array(
      'pmid' => xpath_item($article, "//ArticleIdList/ArticleId[@IdType='pubmed']"),
      'title' => xpath_item($article, "ArticleTitle"),
      'journal' => xpath_item($article, "Journal/Title"),
      'year' => xpath_item($article, "Journal/JournalIssue/PubDate/Year"),
      'abstract' => xpath_item($article, "Abstract/AbstractText"),
      'doi' => xpath_item($article, "//ArticleIdList/ArticleId[@IdType='doi']"),
      'authors' => $authors,
      'raw' => $article,
      );
  }
}
