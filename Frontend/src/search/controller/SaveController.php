<?php

namespace search\controller;
include "config.php";

use MongoClient;

class SaveController
{

	function download($doi,$filename,$response){
		if (isset($response['_source']['DATA'])){
		$file="http://localhost:8081/download/".$doi."/".$filename ;
		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=".$filename);
		$readfile=readfile($file);
		if ($readfile==false) {
			return false;
		}
		else{
			return true;
		}
		exit;
		}
		
	}


	function preview($doi,$filename,$response){
		if (isset($response['_source']['DATA'])){
		$file="http://localhost:8081/download/".$doi."/".$filename ;
		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	header("Content-Disposition: inline; filename=".$filename);
    	foreach ($response['_source']['DATA']['FILES'] as $key => $value) {
    		if ($filename==$value["DATA_URL"]) {
    			$mime=$value["FILETYPE"];
    		}

    	}

    	if ($mime=="pdf") {
    		$readfile=readfile($file);
    		$mime="application/pdf";
    		header('Content-Type:  '.$mime);
    	}
    	elseif ($mime=='csv') {
    		$file = fopen($file, "r");
			$firstTimeHeader = true;
			$firstTimeBody = true;
			echo "<table border='1'>";
			while(! feof($file))
			{
			    $data = fgetcsv($file);
			    
			    if($firstTimeHeader)
			    {
			        echo "<thead>";
			    }
			    else
			    {
			        if($firstTimeBody)
			        {
			            echo "</thead>";
			            echo "<tbody>";
			            $firstTimeBody = false;
			        }
			    }
			    echo "<tr>";
			    
			    foreach ($data as $value)
			    {
			        if($firstTimeHeader)
			        {
			            echo "<th>" . $value . "</th>";
			        }
			        else
			        {
			            echo "<td>" . $value . "</td>";
			        }
			    }
			    
			    echo "</tr>";
			    if($firstTimeHeader)
			    {
			        $firstTimeHeader = false;
			    }
			}
			echo "</table>";
		    	}
		    	elseif ($mime=='txt' OR $mime=='sh' OR $mime=='py') {
		    	$readfile=readfile($file);
    			$mime="text/plain";
    			header('Content-Type:  '.$mime);
		    	}
		    	elseif($mime=='png'){
					$readfile=readfile($file);
		    		$mime="image/png";
		    		header('Content-Type:  '.$mime);
		    	}
		    	elseif($mime=='jpg'){
					$readfile=readfile($file);
		    		$mime="image/jpg";
		    		header('Content-Type:  '.$mime);
		    	}
		    	elseif($mime=='gif'){
					$readfile=readfile($file);
		    		$mime="image/gif";
		    		header('Content-Type:  '.$mime);
		    	}

		    	else{
		    		echo "<h1>Cannot preview file</h1> <p>Sorry, we are unfortunately not able to preview this file.<p>";
		    		$readfile=false;
		    		header('Content-Type:  text/html');
		    	}

				if ($readfile==false) {
					return false;
				}
				else{
					return true;
				}
				exit;
				}
		
	}


	function Newdatasheet()
	{
	$this->db = new MongoClient("mongodb://localhost:27017");
	$required = array('title','creation_date','language','authors-name','authors-firstname','authors-email','description','scientific_field','measurement_nature','measurement_abbreviation','measurement_unit','license');
	foreach($required as $field) {
	  if (empty($_POST[$field])) {
	    $error = true;
	  }
	}
	if ($error) {
	}
	else{
		foreach ($_POST as $key => $value){	
		 if ($key=="creation_date") {
		 $array["CREATION_DATE"]=$value;
		 }
		 if ($key=="title") {
		 $array["TITLE"]=$value;
		 }
		  if ($key=="sampling_date") {
		 $array["SAMPLING_DATE"][]=$value;
		 }
		 if ($key=="language") {
		 	if ($value=='0') {
		 		$language="French";
		 	}
		 	if ($value=="1") {
		 		$language="English";
		 	}
		 	$array["LANGUAGE"]=$language;
		 }
		 if ($key=="scientific_field") {
		 	$array["SCIENTIFIC_FIELD"]["NAME"]=$value;
		 }
		  if ($key=="station_name") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["NAME"]=$value[0];
		 	}
		 }
		  if ($key=="station_coordonate_system") {
			 	if (count($value)>1) {
			 		foreach ($value as $key => $value) {
			 		$array["STATION"][$key]["COORDONATE_SYSTEM"]=$value;
			 		}
			 	}
			 	else{
			 	$array["STATION"][0]["ABBREVIATION"]=$value[0];
			 	}
			 }
		 if ($key=="station_abbreviation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["ABBREVIATION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ABBREVIATION"]=$value[0];
		 	}
		 }
		 if ($key=="station_longitude") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["LONGITUDE"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["LONGITUDE"]=$value[0];
		 	}
		 }
		 if ($key=="station_latitude") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["LATITUDE"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["LATITUDE"]=$value[0];
		 	}
		 }
		 if ($key=="station_elevation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["ELEVATION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ELEVATION"]=$value[0];
		 	}
		 }
		 if ($key=="station_description") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["DESCRIPTION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["DESCRIPTION"]=$value[0];
		 	}
		 }

		  if ($key=="measurement_nature") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["NATURE"]=$value;
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["NATURE"]=$value[0];
		 	}
		 }
		  if ($key=="measurement_abbreviation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["ABBREVIATION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["ABBREVIATION"]=$value[0];
		 	}
		 }
		 if ($key=="measurement_unit") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["UNIT"]=$value;
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["UNIT"]=$value[0];
		 	}
		 }

		 if ($key=="sample_kind") {
		 	$array["SAMPLE_KIND"][]["NAME"]=$value;
		 }
		   if ($key=="institution") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["INSTITUTION"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["INSTITUTION"][0]["NAME"]=$value[0];
		 	}
		 }
		
		  if ($key=="authors-firstname") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["FIRST_NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["FIRST_NAME"]=$value[0];
		 	}
		 }
		 if ($key=="authors-name") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["NAME"]=$value[0];
		 	}
		 }
		  if ($key=="authors-email") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["MAIL"]=$value;
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["MAIL"]=$value[0];
		 	}
		 }
		 if ($key=="keywords") {
		 	if (count($value<=3)) {
		 		foreach ($value as $key => $value) {
		 		$array["KEYWORDS"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["KEYWORDS"]["NAME"]=$value;
		 	}
		 }
		 if ($key=="description") {
		 $array["DATA_DESCRIPTION"]=$value;
		 }
		 if ($key=="license") {
		 $array["LICENSE"]=$value;
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

		 $array["PUBLICATION_DATE"]=$publication_date;
		 
		 }

		 $array["UPLOAD_DATE"]=date('Y-m-d');
		 
		}
        $doi=rand(5, 15000);
	for($i=0; $i<count($_FILES['file']['name']); $i++) {
		$repertoireDestination = UPLOAD_FOLDER;
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
}
	



	function Editdatasheet($collection,$doi)
	{
	$this->db = new MongoClient("mongodb://localhost:27017");
	$required = array('title','creation_date','language','authors-name','authors-firstname','authors-email','description','scientific_field','measurement_nature','measurement_abbreviation','measurement_unit','license');
	foreach($required as $field) {
	  if (empty($_POST[$field])) {
	    $error = true;
	  }
	}
	if ($error) {
		//echo "all are required";
	}
	else{
		foreach ($_POST as $key => $value){	
		 if ($key=="creation_date") {
		 $array["CREATION_DATE"]=$value;
		 }
		 if ($key=="title") {
		 $array["TITLE"]=$value;
		 }
		 if ($key=="sampling_date") {
		 $array["SAMPLING_DATE"][]=$value;
		 }
		 if ($key=="language") {
		 	if ($value=='0') {
		 		$language="FRENCH";
		 	}
		 	if ($value=="1") {
		 		$language="FRENCH";
		 	}
		 	$array["LANGUAGE"]=$language;
		 }
		 if ($key=="scientific_field") {
		 	$array["SCIENTIFIC_FIELD"]["NAME"]=$value;
		 }
		
		  if ($key=="station_name") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["NAME"]=$value[0];
		 	}
		 }
		  if ($key=="station_coordonate_system") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["COORDONATE_SYSTEM"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ABBREVIATION"]=$value[0];
		 	}
		 }
		 if ($key=="station_abbreviation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["ABBREVIATION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ABBREVIATION"]=$value[0];
		 	}
		 }
		 if ($key=="station_longitude") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["LONGITUDE"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["LONGITUDE"]=$value[0];
		 	}
		 }
		 if ($key=="station_latitude") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["LATITUDE"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["LATITUDE"]=$value[0];
		 	}
		 }
		 if ($key=="station_elevation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["ELEVATION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["ELEVATION"]=$value[0];
		 	}
		 }
		 if ($key=="station_description") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["STATION"][$key]["DESCRIPTION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["STATION"][0]["DESCRIPTION"]=$value[0];
		 	}
		 }

		  if ($key=="measurement_nature") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["NATURE"]=$value;
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["NATURE"]=$value[0];
		 	}
		 }
		  if ($key=="measurement_abbreviation") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["ABBREVIATION"]=$value;
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["ABBREVIATION"]=$value[0];
		 	}
		 }
		 if ($key=="measurement_unit") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["MEASUREMENT"][$key]["UNIT"]=$value;
		 		}
		 	}
		 	else{
		 	$array["MEASUREMENT"][0]["UNIT"]=$value[0];
		 	}
		 }

		   if ($key=="institution") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["INSTITUTION"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["INSTITUTION"][0]["NAME"]=$value[0];
		 	}
		 }
		 if ($key=="sample_kind") {
		 	$array["SAMPLE_KIND"][]["NAME"]=$value;
		 }

		 if ($key=="authors-firstname") {
			 	if (count($value)>1) {
			 		foreach ($value as $key => $value) {
			 		$array["FILE_CREATOR"][$key]["FIRST_NAME"]=$value;
			 		}
			 	}
			 	else{
			 	$array["FILE_CREATOR"][0]["FIRST_NAME"]=$value[0];
			 	}
			 }


		 if ($key=="authors-name") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["NAME"]=$value[0];
		 	}
		 }
		  if ($key=="authors-email") {
		 	if (count($value)>1) {
		 		foreach ($value as $key => $value) {
		 		$array["FILE_CREATOR"][$key]["MAIL"]=$value;
		 		}
		 	}
		 	else{
		 	$array["FILE_CREATOR"][0]["MAIL"]=$value[0];
		 	}
		 }
		 if ($key=="keywords") {
		 	if (count($value<=3)) {
		 		foreach ($value as $key => $value) {
		 		$array["KEYWORDS"][$key]["NAME"]=$value;
		 		}
		 	}
		 	else{
		 	$array["KEYWORDS"]["NAME"]=$value;
		 	}
		 }
		 if ($key=="description") {
		 $array["DATA_DESCRIPTION"]=$value;
		 }
		 if ($key=="license") {
		 $array["LICENSE"]=$value;
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

		 $array["PUBLICATION_DATE"]=$publication_date;
		 
		 }

		 $array["UPLOAD_DATE"]=date('Y-m-d');
		 
		}
	}
		$collectionObject = $this->db->selectCollection('ORDaR', $collection);
		if (is_numeric($doi)==true) {
			$doi=$doi;

		// Quand les vrai DOI seront implanté il faudra modifier ici

		$collectionObject->update(array('_id' => new \MongoInt32($doi)),array('$set'=>array("INTRO" => $array)));
		}
		else{
	        $newdoi=rand(5, 15000);
	        mkdir(UPLOAD_FOLDER.$newdoi,0777,true);
	        $query=array('_id' => $doi);
	       	$cursor = $collectionObject->find($query);
	       	foreach ($cursor as $key => $value) {
	       		$ORIGINAL_DATA_URL=$value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
	       	}
	        unlink($ORIGINAL_DATA_URL);
	        //unlink(UPLOAD_FOLDER.$doi.'/'.$doi.'_INTRO.csv')
			//$collectionObject->insert(array('_id' => new \MongoInt32($doi)),array("INTRO" => $array));
	        rename(UPLOAD_FOLDER.$doi.'/'.$doi.'_DATA.csv',UPLOAD_FOLDER.$newdoi."/".$doi.'_DATA.csv');
	        rmdir(UPLOAD_FOLDER.$doi);
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
	if (is_numeric($doi)==true) {
		print("test");
	}
	else{
	$this->db = new MongoClient("mongodb://localhost:27017");
	$collectionObject = $this->db->selectCollection('ORDaR', $collection);
	$query=array('_id' => $doi);
   	$cursor = $collectionObject->find($query);
   	foreach ($cursor as $key => $value) {
   		$ORIGINAL_DATA_URL=$value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
   	}
   	unlink($ORIGINAL_DATA_URL);
	$collectionObject->remove(array('_id' => $doi));
	unlink(UPLOAD_FOLDER.$doi.'/'.$doi.'_DATA.csv');
	rmdir(UPLOAD_FOLDER.$doi);
	print("erase");
	}



}


}

























?>