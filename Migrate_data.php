<?php

$db = new MongoClient("mongodb://localhost:27017");
$collectionOrdar= $db->selectCollection('ORDaR', 'Mobised');
$collectionMOBISED= $db->selectCollection('MOBISED', 'sediment');

$query=array('INTRO.ACCESS_RIGHT' => 'Unpublished');
$cursorOrdar = $collectionOrdar->find($query);

$query=array('INTRO.ACCESS_RIGHT' => 'Unpublished');
$cursorMOBISED = $collectionMOBISED->find($query);


// Suppression unpublished Ordar
foreach ( $cursorOrdar as $id => $value )
{
//$collectionOrdar->remove(array('_id' => $value['_id']));
}


foreach ( $cursorMOBISED as $id => $value )
{
	echo "$id :   ";
	$collectionOrdar->insert()
}




