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
    	elseif ($mime=='txt' OR $mime=='csv') {
    		$readfile=readfile($file);
    		$mime="text/plain";
    		header('Content-Type:  '.$mime);
    	}
    	elseif($mime=='png'){
			$readfile=readfile($file);
    		$mime="image/png";
    		header('Content-Type:  '.$mime);
    	}
    	elseif($mime =='zip'){
    		     $mime="application/zip";
    		    // $url = $file;
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

	function mime2ext($mime){
  $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel","text\/csv"],"json":["application\/json","text\/json"]}';
  $all_mimes = json_decode($all_mimes,true);
  foreach ($all_mimes as $key => $value) {
    if(array_search($mime,$value) !== false) return $key;
  }
  return false;
}


	function Newdatasheet()
	{
	$this->db = new MongoClient("mongodb://localhost:27017");
	foreach ($_POST as $key => $value){	
	 if ($key=="creation_date") {
	 $array["CREATION_DATE"]=$value;
	 }
	 if ($key=="title") {
	 $array["TITLE"]=$value;
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
	 		$array["KEYWORDS"][$key]=$value;
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
	 if ($key=="sample_kind") {
	 	$array["SAMPLE_KIND"]["NAME"]=$value;
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
			
			                $type=mime_content_type($repertoireDestination.$doi."/".$nomDestination);
			                $filetype=self::mime2ext($type);
							if ($filetype==!false) {
					            $filetypes=$filetype;
					        }
					         else {
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

























?>