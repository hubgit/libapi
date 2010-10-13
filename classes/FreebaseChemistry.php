<?php

class FreebaseChemistry extends Freebase{
  public $doc = 'http://wiki.freebase.com/wiki/Reconciliation';

  function build_reconciliation_query($args){
    $parts = array();

    if ($args['chem:smiles'])
      $parts['/chemistry/chemical_compound/structure_smiles'] = $args['chem:smiles'];
    if ($args['iupac:inchi'])
      $parts['/chemistry/chemical_compound/structure_inchi'] = $args['iupac:inchi'];
    if ($args['iupac:stdinchi'])
      $parts['/chemistry/chemical_compound/structure_inchi'] = $args['iupac:stdinchi'];
    if ($args['chem:molecular-formula'])
      $parts['/chemistry/chemical_compound/formula'] = $args['chem:molecular-formula'];
    if ($args['pubchem:cid'])
      $parts['/chemistry/chemical_compound/pubchem_id'] = $args['pubchem:cid'];
    if ($args['iupac:inchikey'])
      $parts['/chemistry/chemical_compound/inchikey'] = $args['iupac:inchikey'];
    if ($args['iupac:stdinchikey'])
      $parts['/chemistry/chemical_compound/inchikey'] = $args['iupac:stdinchikey'];
    if ($args['iupac:name'])
      $parts['/chemistry/chemical_compound/iuapc_id'] = $args['iupac:name'];
    if ($args['cas:id'])
      $parts['/chemistry/chemical_compound/cas_id'] = $args['cas:id'];
    if ($args['dc:title'])
      $parts['/type/object/name'] = $args['dc:title'];

    if (empty($parts))
      return false;

    if ($args['freebase:type'])
      $parts['/type/object/type'] = $args['freebase:type'];

    return $parts;
  }

  function fetch($ids = null, $params = array()){
    if (!is_array($ids))
      $ids = array($ids);

    if (empty($ids))
        return false;

    $q = array();
    $i = 0;
    foreach ($ids as $id){
       $q['q' . $i++] = array(
         'query' => array(
            'id' => $id,
            '/type/object/type' => '/chemistry/chemical_compound',
            '/medicine/drug/drugbank' => array(),
            '/chemistry/chemical_compound/pubchem_id' => array(),
            '/chemistry/chemical_compound/structure_smiles' => array(),
            '/chemistry/chemical_compound/structure_inchi' => array(),
            '/chemistry/chemical_compound/formula' => array(),
            '/chemistry/chemical_compound/inchikey' => array(),
            '/chemistry/chemical_compound/iuapc_id' => array(),
            '/chemistry/chemical_compound/cas_id' => array(),
            'name' => array(),
            '/common/topic/alias' => array(),
         ),
      );
    }

    $this->get_data('http://api.freebase.com/api/service/mqlread', array('queries' => json_encode($q)), 'json');

    $items = array();
    foreach ($this->data as $key => $item){
       if (preg_match('/q\d+/', $key))
         $items[] = $this->convert_data($item->result);
    }
    return $items;
  }

  function convert_data($item){
    return array(
      'chem:smiles' => $item->{'/chemistry/chemical_compound/structure_smiles'}[0],
      'iupac:inchi' => $item->{'/chemistry/chemical_compound/structure_inchi'}[0],
      'chem:molecular-formula' => $item->{'/chemistry/chemical_compound/formula'}[0],
      'pubchem:cid' => $item->{'/chemistry/chemical_compound/pubchem_id'}[0],
      'iupac:inchikey' => $item->{'/chemistry/chemical_compound/inchikey'}[0],
      'iupac:name' => $item->{'/chemistry/chemical_compound/iuapc_id'}[0],
      'misc:synonyms' => $item->{'/common/topic/alias'},
      'dc:title' => $item->{'name'}[0],
      'drugbank:id' => $item->{'/medicine/drug/drugbank'}[0],
      'freebase:id' => $item->{'id'},
      'cas:id' => $item->{'/chemistry/chemical_compound/cas_id'}[0],
      'rdf:uri' => 'http://www.freebase.com' . $item->{'id'},
    );
  }
}

