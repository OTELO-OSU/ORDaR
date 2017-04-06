<?php

$db = new MongoClient("mongodb://localhost:27017");
$config = parse_ini_file('Frontend/config.ini');
$bdd      = strtolower($config['authSource']);
$db = $db->selectDB($bdd);

$collections = $db->getCollectionNames();

foreach ($collections as $collection) {
	$collection= $db->selectCollection($collection);
	$query=array('INTRO.ACCESS_RIGHT' => 'Embargoed');
	$cursor = $collection->find($query);
	foreach ( $cursor as $id => $value )
	{
	    $now= Date('Y-m-d');
	    $embargoeddate=$value['INTRO']['PUBLICATION_DATE'];
	    if($embargoeddate <= $now) {
	    	$update = $collection->update(array("_id" => $value['_id']), array('$set' => array("INTRO.ACCESS_RIGHT" => "Open")));
	    }

	}
}
