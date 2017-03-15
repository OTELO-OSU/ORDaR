<?php

namespace search\controller;


Class FileController{


function download($doi,$filename,$response){
		$config = parse_ini_file("config.ini");
		$portapache=$config['portApache'];
		if (isset($response['_source']['DATA'])){
		$file="http://127.0.0.1:".$portapache."/download/".$doi."/".$filename ;
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
		$config = parse_ini_file("config.ini");
		$portapache=$config['portApache'];
		if (isset($response['_source']['DATA'])){
		$file="http://127.0.0.1:".$portapache."/download/".$doi."/".$filename ;
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
			echo '<link rel="stylesheet" type="text/css" href="/css/semantic/dist/semantic.min.css">';
			echo '	<link rel="stylesheet" type="text/css" href="/css/style.css">
';
			echo "<div class='' ui grid container'  style='overflow-x:auto'><table style='width:700px; height:500px;' class='ui compact unstackable table'></div>";
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

}