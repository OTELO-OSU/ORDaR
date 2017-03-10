<?php

namespace search\controller;


use MongoClient;

class DatasheetController
{

/**
 * Create a mongo connection instance
 * @return Mongo_instance
 */
function connect_tomongo(){
			$config = parse_ini_file("config.ini");

	 if(empty($config['username']) && empty($config['password'])) {
                $this->db = new MongoClient("mongodb://" . $config['host'] . ':' . $config['port']);
            } else {
                $this->db= new MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array('authSource' => $config['authSource'],'username' => $config['username'], 'password' => $config['password']));
            }
            return $this->db;
	}
	/**
	 * Parse Post Data 
	 * @param array, post request
	 * @return array, parsed data to write
	 */
	function Postprocessing($POST){
	$error=null;
	$config = parse_ini_file("config.ini");
	$UPLOAD_FOLDER=$config["UPLOAD_FOLDER"];
	$required = array('title','creation_date','language','authors_name','authors_firstname','authors_email','description','scientific_field','measurement_nature','measurement_abbreviation','measurement_unit','license');
	foreach($required as $field) {
	  if (empty($_POST[$field])) {
	  	print $field;
	    $error = true;
	  }
	}
	if ($error==true) {
	}
	else{
		foreach ($POST as $key => $value){	
		 if ($key=="creation_date") {
		 $array["CREATION_DATE"]=htmlspecialchars($value, ENT_QUOTES);
		 }
		 if ($key=="title") {
		 $array["TITLE"]= htmlspecialchars($value, ENT_QUOTES);
		 }
		  if ($key=="sampling_date") {
		 $array["SAMPLING_DATE"][]=htmlspecialchars($value, ENT_QUOTES);
		 }
		 if ($key=="language") {
		 	if ($value=='0') {
		 		$language="FRENCH";
		 	}
		 	if ($value=="1") {
		 		$language="ENGLISH";
		 	}
		 	$array["LANGUAGE"]=$language;
		 }
		if ($key=="scientific_field") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["SCIENTIFIC_FIELD"][$key]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["SCIENTIFIC_FIELD"][0]["NAME"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		  if ($key=="station_name") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["NAME"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		  if ($key=="station_coordonate_system") {
			 	if (count($value)>1) {
			 		foreach ($value as $key => $value) {
			 		$array["STATION"][$key]["COORDONATE_SYSTEM"]=htmlspecialchars($value, ENT_QUOTES);;
			 		}
			 	}
			 	else{
			 	$array["STATION"][0]["ABBREVIATION"]=htmlspecialchars($value[0], ENT_QUOTES);
			 	}
			 }
		 if ($key=="station_abbreviation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["ABBREVIATION"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ABBREVIATION"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="station_longitude") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["LONGITUDE"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["LONGITUDE"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="station_latitude") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["LATITUDE"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["LATITUDE"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="station_elevation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["ELEVATION"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ELEVATION"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="station_description") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["DESCRIPTION"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["DESCRIPTION"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }

		  if ($key=="measurement_nature") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["NATURE"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["NATURE"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		  if ($key=="measurement_abbreviation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["ABBREVIATION"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["ABBREVIATION"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="measurement_unit") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["UNIT"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["UNIT"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }

		 if ($key=="sample_kind") {
		 	$array["SAMPLE_KIND"][]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 }
		   if ($key=="institution") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["INSTITUTION"][$key]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["INSTITUTION"][0]["NAME"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		
		  if ($key=="authors_firstname") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["FIRST_NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["FIRST_NAME"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="authors_name") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["NAME"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		  if ($key=="authors_email") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["MAIL"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["MAIL"]=htmlspecialchars($value[0], ENT_QUOTES);
		 	}
		 }
		 if ($key=="keywords") {
		 	if (count($value<=3)) {
		 		foreach ($value as $key => $value) {
		 		$array["KEYWORDS"][$key]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 		}
		 	}
		 	else{
		 	$array["KEYWORDS"]["NAME"]=htmlspecialchars($value, ENT_QUOTES);
		 	}
		 }
		 if ($key=="description") {
		 $array["DATA_DESCRIPTION"]=htmlspecialchars($value, ENT_QUOTES);
		 }
		if ($key=="license") {
			$licensetype=null;
		 	if ($value==1) {
		 		$licensetype="Attribution alone (CC BY)";
		 	}
		 	elseif ($value==2) {
		 		$licensetype="Attribution + ShareAlike (CC BY-SA)";
		 	}
		 	elseif ($value==3) {
		 		$licensetype="Attribution + Noncommercial (CC BY-NC)";
		 	}
		 	elseif ($value==4) {
		 		$licensetype="Attribution + NoDerivatives (CC BY-ND)";
		 	}
		 	elseif ($value==5) {
		 		$licensetype="Attribution + Noncommercial + ShareAlike (CC BY-NC-SA)";
		 	}
		 	elseif ($value==6) {
		 		$licensetype="Attribution + Noncommercial + NoDerivatives (CC BY-NC-ND)";
		 	}
		 $array["LICENSE"]=$licensetype;
		 }
		 if ($key=="access_right") {
		 $array["ACCESS_RIGHT"]=$value;
		 	if ($value=="Closed") {
		 		$publication_date="9999-12-31";
		 	}
		 	if ($value=="Open") {
		 		$publication_date=date('Y-m-d');
		 	}
		 	if ($value=="Embargoed") {
		 		$publication_date=$_POST["publication_date"];
		 	}

		 
		 }

		 $array["UPLOAD_DATE"]=date('Y-m-d');
		 
		}
		return $array;
	}
}

	
	function Newdatasheet($db,$array)
	{
	$config = parse_ini_file("config.ini");
	$UPLOAD_FOLDER=$config["UPLOAD_FOLDER"];
	$error=NULL;
	//$this->db = new MongoClient("mongodb://localhost:27017");
    $doi=rand(5, 15000);
	for($i=0; $i<count($_FILES['file']['name']); $i++) {
		$repertoireDestination = $UPLOAD_FOLDER;
		$nomDestination   = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
		$data["FILES"][$i]["DATA_URL"]=$nomDestination;
		if (file_exists($repertoireDestination.$_FILES["file"]["name"][$i])){
			return false;
		}
		else{
			if (is_uploaded_file($_FILES["file"]["tmp_name"][$i])) {
				if (!file_exists($repertoireDestination.$doi)) {
				mkdir($repertoireDestination.$doi);
			}
			    if (rename($_FILES["file"]["tmp_name"][$i],$repertoireDestination.$doi."/".$nomDestination)) {
					        $extension= new \SplFileInfo($repertoireDestination.$doi."/".$nomDestination);
					        $filetypes=$extension->getExtension();
					        if (strlen($filetypes)==0 OR strlen($filetypes)>4) {
					        	$filetypes='unknow';
					        }
				$data["FILES"][$i]["FILETYPE"]=$filetypes;
				$collection ="Manual_Depot";
			    $collectionObject = $this->db->selectCollection('ORDaR', $collection);
				$json=array('_id' => $doi, "INTRO" => $array,"DATA" => $data);//voir pour DOI
			    }         
			} 
		}
	}

				$collectionObject->insert($json);
				return true;
	}





	function Editdatasheet($collection,$doi,$db;$array){
	$config = parse_ini_file("config.ini");
	$UPLOAD_FOLDER=$config["UPLOAD_FOLDER"];
	$error=NULL;
	//$this->db = new MongoClient("mongodb://localhost:27017");
		$collectionObject = $this->db->selectCollection('ORDaR', $collection);
		if (is_numeric($doi)==true) {
			$doi=$doi;

		// Quand les vrai DOI seront implanté il faudra modifier ici
			
		$collectionObject->update(array('_id' => new \MongoInt32($doi)),array('$set'=>array("INTRO" => $array)));
		}
		else{
	        $newdoi=rand(5, 15000);
	        mkdir($UPLOAD_FOLDER.$newdoi,0777,true);
	        $query=array('_id' => $doi);
	       	$cursor = $collectionObject->find($query);
	       	foreach ($cursor as $key => $value) {
	       		$ORIGINAL_DATA_URL=$value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
	       	}
	        unlink($ORIGINAL_DATA_URL);
	        //unlink(UPLOAD_FOLDER.$doi.'/'.$doi.'_INTRO.csv')
			//$collectionObject->insert(array('_id' => new \MongoInt32($doi)),array("INTRO" => $array));
	        rename($UPLOAD_FOLDER.$doi.'/'.$doi.'_DATA.csv',$UPLOAD_FOLDER.$newdoi."/".$doi.'_DATA.csv');
	        rmdir($UPLOAD_FOLDER.$doi);
			$collectionObject->update(array('_id' => $doi),array('$set'=>array("INTRO" => $array)));
			$olddata = $collectionObject->find(array('_id' => $doi));
			foreach ($olddata as $key => $value) {
				$INTRO=$value["INTRO"];
				$value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"]=NULL;
				$DATA=$value["DATA"];
			}
			$collectionObject->remove(array('_id' => $doi));
			$collectionObject->insert(array('_id' => $newdoi,"INTRO" => $INTRO,"DATA" => $DATA));	
		}
		return true;
	}
	
	
function removeUnpublishedDatasheet($collection,$doi){
		$config = parse_ini_file("config.ini");
		$UPLOAD_FOLDER=$config["UPLOAD_FOLDER"];
		if (is_numeric($doi)==true) {
		print("test"); // test de suppression si publier
	}
	else{
		//$this->db = new MongoClient("mongodb://localhost:27017");
		$db=self::connect_tomongo();
		$collectionObject = $this->db->selectCollection('ORDaR', $collection);
		$query=array('_id' => $doi);
	   	$cursor = $collectionObject->find($query);
	   	foreach ($cursor as $key => $value) {
	   		$ORIGINAL_DATA_URL=$value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
	   	}
	   	unlink($ORIGINAL_DATA_URL);
		$collectionObject->remove(array('_id' => $doi));
		unlink($UPLOAD_FOLDER.$doi.'/'.$doi.'_DATA.csv');
		rmdir($UPLOAD_FOLDER.$doi);
		print("erase");
		}

	}




	function Send_Mail_author($doi,$response,$author_name,$author_firstname,$object,$message,$sendermail){
		$title=$response['_source']['INTRO']['TITLE'];
		foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
			if ($author_name==$value["NAME"]&&$author_firstname==$value["FIRST_NAME"]) {
				$mail=$value["MAIL"];
				//mail('guiot.anthony@free.fr', 'Mon Sujet', "test");
				echo $value["MAIL"];
				mail($mail, 'Contact from ORDaR :'.$object, $sendermail." want to know something about this dataset: <br>DOI: ".$doi."<br>Title: ".$title." <br> Message from ".$sendermail.": <br> ".$message, 'From:'.$sendermail);
			}
		}
		if (!empty($mail)) {
			
		}
		else{
			echo "an error as occured!";
		}

		}
}




























?>