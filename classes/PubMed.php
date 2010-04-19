<?php

class PubMed extends API {
  public $doc = 'http://www.ncbi.nlm.nih.gov/entrez/query/static/eutils_help.html';
  
  public $webenv;
  public $querykey;
  
  public $n = 500;
   
  function search_soap($q, $params = array()){
    unset($this->webenv, $this->querykey);
    
    $default = array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'usehistory' => 'y',
      'retmax' => 1,
      'term' => $q,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      );
      
    $this->soap('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/eutils.wsdl', 'run_eSearch', array_merge($default, $params));
    
    $this->total = $this->data->Count;
    $this->webenv = $this->data->WebEnv;
    $this->querykey = $this->data->QueryKey;
    
    return $this->data;
  }
  
  function fetch_soap($ids = NULL, $params = array()){
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

    $this->soap('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/efetch_pubmed.wsdl', 'run_eFetch', array_merge($default, $params));
    
    return $this->data;
  }
  
  function related_soap($pmids, $params = array()){
    if (!is_array($pmids))
      $pmids = array($pmids);
    
    $default = array(
       'db' => 'pubmed',
       'dbfrom' => 'pubmed',
       'id' => implode(',', $pmids),
       'retmode' => 'xml',
       'tool' => Config::get('EUTILS_TOOL'),
       'email' => Config::get('EUTILS_EMAIL'),
     );
     
    $this->soap('http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/eutils.wsdl', 'run_eLink', array_merge($default, $params));
    
    $this->results = array();
    //debug($this->data->LinkSet);
    foreach ($this->data->LinkSet[0]->LinkSetDb[0]->Link as $link)
      $this->results[] = $link->Id->{'_'};
    
    $this->total = count($this->results);
  }
  
  function search($q, $params = array()){
    unset($this->webenv, $this->querykey);

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

    $this->total = $this->xpath->query("Count")->item(0)->nodeValue;
    $this->webenv = $this->xpath->query("WebEnv")->item(0)->nodeValue;
    $this->querykey = $this->xpath->query("QueryKey")->item(0)->nodeValue;
    
    return $this->data;
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
    return $this->data;
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
     
    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi', array_merge($default, $params), 'dom');
    
    $this->results = array();
    foreach ($this->xpath->query("LinkSet/LinkSetDb/Link") as $link)
      $this->results[] = $this->xpath->query("Id", $link)->item(0)->nodeValue;
    
    $this->total = count($items);
  }
  
  function fulltext($pmid){
    $params = array(
      'db' => 'pubmed', 
      'retmode' => 'xml', 
      'id' => $pmid,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
    );
    
    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', $params, 'dom');
    
    $nodes = $this->xpath->query("DocSum/Item[@Name='DOI']");
    return $node->length ? 'http://dx.doi.org/' . $nodes->item(0)->nodeValue : 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?cmd=prlinks&dbfrom=pubmed&retmode=ref&id=' . $pmid;
  }

  function content($term, $max = 0, $from = 0){
    /*
    $args = filter_var_array($args, array(
      'max' => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => FILTER_REQUIRE_SCALAR,
        'options' => array('default' => 10000000), // TODO: is there a limit?
       ),
    ));
    */
  
    $from = $this->get_latest($from, 0); // 0 = 1970-01-01T00:00:00Z

    $to = date('Y/m/d', time() + 60*60*24*365*10); // 10 years in future

    $count = 0;
    $n = $max ? min($this->n, $max) : $this->n;

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
          $status = $medline->getAttribute('Status');

          if ($this->output_dir)
            $article->save(sprintf('%s/%d.xml', $this->output_dir, $id));
          else
            $this->results[$id] = $article;
        }
  
        //sleep(1);
    
        $start += $n;
      } while ($start < min($max, $this->total));
    }
    
    if ($this->output_dir)
      file_put_contents($this->output_dir . '/latest', date('Y/m/d'));
  }
  
  // fetch an individual item from PubMed by DOI or PMID
  // TODO: clean up
  function metadata($pmid, $data){
    if (!$pmid && $data['doi']){
      $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', array(
        'db' => 'pubmed',
        'retmode' => 'xml',
        'retmax' => 1,
        'usehistory' => 'n',
        'term' => $data['doi'] . '[DOI]',
        'tool' => Config::get('EUTILS_TOOL'),
        'email' => Config::get('EUTILS_EMAIL'),
        ), 'dom');
      
      
      if ($this->xpath->query('Count')->nodeValue > 0)  
        $pmid = $this->xpath->query('IdList/Id')->item(0)->nodeValue;
    }

    if (!$pmid)
      return FALSE;

    $this->get_data('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', array(
      'db' => 'pubmed',
      'retmode' => 'xml',
      'id' => $pmid,
      'tool' => Config::get('EUTILS_TOOL'),
      'email' => Config::get('EUTILS_EMAIL'),
      ), 'dom');

    $article = $this->xpath->query('PubmedArticle/MedlineCitation/Article')->item(0);

    $doi = $this->xpath->query("//ArticleIdList/ArticleId[@IdType='doi']", $article)->item(0)->nodeValue;
    $pmid = $this->xpath->query("//ArticleIdList/ArticleId[@IdType='pubmed']", $article)->item(0)->nodeValue;

    $authors = array();
    foreach ($this->xpath->query("AuthorList/Author", $article) as $author)
      $authors[] = implode(' ', array($this->xpath->query('Initials', $author)->item(0)->nodeValue, $this->xpath->query('LastName', $author)->item(0)->nodeValue));

    return array(
      'pmid' => $this->xpath->query("//ArticleIdList/ArticleId[@IdType='pubmed']", $article)->item(0)->nodeValue,
      'title' => $this->xpath->query("ArticleTitle", $article)->item(0)->textContent,
      'journal' => $this->xpath->query("Journal/Title", $article)->item(0)->textContent,
      'year' => $this->xpath->query("Journal/JournalIssue/PubDate/Year", $article)->item(0)->nodeValue,
      'abstract' => $this->xpath->query("Abstract/AbstractText", $article)->item(0)->textContent,
      'doi' => $this->xpath->query("//ArticleIdList/ArticleId[@IdType='doi']", $article)->item(0)->nodeValue,
      'authors' => $authors,
      'raw' => $article,
      );
  }
}
