<?php

class DrugBank extends API {
    public $doc = 'http://www.drugbank.ca/search/extractor';
    public $cache = TRUE;

    public $n = 10;

    function build_query($args){
      if ($args['dc:title'])
        return array('drugs_synonyms', $args['dc:title']);
      else if ($args['chem:molecular-formula'])
        return array('drugs_chemical_formula', $args['chem:molecular-formula']);
      else if ($args['iupac:stdinchi'])
        return array('drugs_inchi_identifier', preg_replace('/^InChI=1S/', 'InChI=1', $args['iupac:stdinchi']));
      else if ($args['iupac:stdinchikey'])
        return array('drugs_inchi_key', $args['iupac:stdinchikey']);
      else if ($args['iupac:inchi'])
        return array('drugs_inchi_identifier', $args['iupac:inchi']);
      else if ($args['iupac:inchikey'])
        return array('drugs_inchi_key', $args['iupac:inchikey']);
      else if ($args['iupac:name'])
        return array('drugs_iupac', $args['iupac:name']);
      else if ($args['chem:smiles'])
        return array('drugs_smiles_canonical', $args['chem:smiles']);
      else if ($args['chebi:id'])
        return array('drugs_chebi_id', $args['chebi:id']);
      else if ($args['pubchem:cid'])
        return array('drugs_pubchem_compound_id', $args['pubchem:cid']); // FIXME: not an exact search!
      else if ($args['cas:id'])
        return array('drugs_cas_number', $args['cas:id']);
      // TODO: structure search using SMILES
      else
        return false;
    }

    function search($args, $params = array()){
      list($field, $term) = $this->build_query($args);

      if (!($field && $term))
        return false;

      $defaults = array(
        'select_drugs_cas_number' => 'on',
        'select_drugs_chebi_id' => 'on',
        'select_drugs_chemical_formula' => 'on',
        'select_drugs_inchi_identifier' => 'on',
        'select_drugs_inchi_key' => 'on',
        'select_drugs_iupac' => 'on',
        'select_drugs_kegg_compound_id' => 'on',
        'select_drugs_kegg_drug_id' => 'on',
        'select_drugs_pubchem_compound_id' => 'on',
        'select_drugs_smiles_canonical' => 'on',
        'select_drugs_synonyms' => 'on',
        'select_drugs_wikipedia_link' => 'on',
        'type' => 'all',
        'join' => 'OR',
        'format' => 'csv',
        $field => $term,
        );

      $this->get_data('http://www.drugbank.ca/cgi-bin/extractor_runner.cgi', array_merge($defaults, $params), 'csv');

      $headings = array_shift($this->data);

      $this->items = array();
      foreach ($this->data as $item){
        $data = array_combine($headings, $item);

        $this->items[] = array(
          'drugbank:id' => $data['drugs.drugbank_id'],
          'pubchem:cid' => $data['drugs.pubchem_compound_id'],
          'chebi:id' => preg_replace('/^CHEBI:/', '', $data['drugs.chebi_id']),
          'dc:title' => $data['drugs.name'],
          'iupac:inchi' => $data['drugs.inchi_identifier'],
          'iupac:inchikey' => preg_replace('/^InChIKey=/', '', $data['drugs.inchi_key']),
          'chem:molecular-formula' => $data['drugs.chemical_formula'],
          'misc:synonyms' => explode('; ', $data['drugs.synonyms']),
          'iupac:name' => $data['drugs.iupac'],
          'misc:image' => 'http://129.128.185.122/drugbank2/drugs/' . urlencode($data['drugs.drugbank_id']) . '/structure_image',
          'rdf:uri' => 'http://www.drugbank.ca/drugs/' . urlencode($data['drugs.drugbank_id']),
          );
      }

      $this->total = count($this->items);
      return $this->items;
    }
}

