<?php

namespace search\controller;



class RequestController
{
      /**
     * Make a curl request
     * @param url to request,option
     * @return data of request
     */
    function Curlrequest($url, $curlopt)
    {
        
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $rawData = curl_exec($ch);
        curl_close($ch);
        return $rawData;
    }
     /**
     * Check status of datacite service
     * @return Code of request
     */
    function Check_status_datacite(){
        $handle = curl_init("https://mds.datacite.org");
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        return $httpCode;
    }
    
     /**
     * Make a request to elasticsearch API
     * @param query of user
     * @return data of request
     */
    function requestToAPI($query)
    {
        $query                      = rawurlencode($query);
        $postcontent                = '{ 
            "aggs" : {  
                "sample_kind" : {  
                    "terms" : {  
                      "field" : "INTRO.SAMPLE_KIND.NAME" 
                    } 
                }, 
                "keywords" : {  
                    "terms" : {  
                      "field" : "INTRO.KEYWORDS.NAME" 
                    } 
                }, 
                 "authors" : {  
                    "terms" : {  
                      "field" : "INTRO.FILE_CREATOR.DISPLAY_NAME" 
                    } 
                }, 
                "scientific_field" : {  
                    "terms" : {  
                      "field" : "INTRO.SCIENTIFIC_FIELD.NAME" 
                    } 
                }, 
                "date" : {  
                    "terms" : {  
                      "field" : "INTRO.CREATION_DATE" 
                    } 
                }, 
                "language" : {  
                    "terms" : {  
                      "field" : "INTRO.LANGUAGE" 
                    } 
                }, 
                "filetype" : {  
                    "terms" : {  
                      "field" : "DATA.FILES.FILETYPE" 
                    } 
                }, 
                 "access_right" : {  
                    "terms" : {  
                      "field" : "INTRO.ACCESS_RIGHT" 
                    } 
                } 
            } 
        }';
        $url                        = 'http://localhost/ordar/_search?q=' . $query . '%20AND%20NOT%20INTRO.ACCESS_RIGHT:Unpublished&size=10000';
        $curlopt                    = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => 9200,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postcontent
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, TRUE);
        $responses["hits"]["total"] = $response["hits"]["total"];
        $responses['aggregations']  = $response['aggregations'];
        foreach ($response["hits"]["hits"] as $key => $value) {
            $responses["hits"]["hits"][$key]           = $value["_source"]["INTRO"];
            $responses["hits"]["hits"][$key]["_index"] = $value["_index"];
            $responses["hits"]["hits"][$key]["_id"]    = $value["_id"];
            $responses["hits"]["hits"][$key]["_type"]  = $value["_type"];
        }
        ;
        $responses = json_encode($responses);
        return $responses;
        
    }

     /**
     * Send generated XML to datacite to save DOI
     * @param xml a envoyer,doi concerné
     * @return treu if ok else false
     */
    function send_XML_to_datacite($XML,$doi){
    	$config        = parse_ini_file("config.ini");
    	$url= "https://mds.datacite.org/metadata/";
        $curlopt                = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $XML,
            CURLOPT_HTTPHEADER => array(
   			"authorization: Basic SU5JU1QuT1RFTE86MFTigqxsb0BkMCE=",
   			'Content-Type: text/xml'
   			),
        );

        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $rawData = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code']=="201") {
        	$url_doi=urlencode($config['URL_DOI']."/record?id=".$doi);
        	$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://mds.datacite.org/doi",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "doi=".$doi."&url=".$url_doi,
			  CURLOPT_HTTPHEADER => array(
			    "authorization: Basic SU5JU1QuT1RFTE86MFTigqxsb0BkMCE=",
			    "cache-control: no-cache",
			  ),
			));

		$response = curl_exec($curl);
		return "true";
        }
        else{
        	return "false";
        }
        


    }

    
    /**
     * Make a request to elasticsearch API by user
     * @param mail of authors,name of auhtors, query of user
     * @return data of request
     */
    function getPublicationsofUser($author_mail, $authors_name, $query)
    {
        $postcontent = '{ 
            "aggs" : {  
                "sample_kind" : {  
                    "terms" : {  
                      "field" : "INTRO.SAMPLE_KIND.NAME" 
                    } 
                }, 
                "keywords" : {  
                    "terms" : {  
                      "field" : "INTRO.KEYWORDS.NAME" 
                    } 
                }, 
                 
                 "authors" : {  
                    "terms" : {  
                      "field" : "INTRO.FILE_CREATOR.DISPLAY_NAME" 
                    } 
                }, 
                "scientific_field" : {  
                    "terms" : {  
                      "field" : "INTRO.SCIENTIFIC_FIELD.NAME" 
                    } 
                }, 
                "date" : {  
                    "terms" : {  
                      "field" : "INTRO.CREATION_DATE" 
                    } 
                }, 
                "language" : {  
                    "terms" : {  
                      "field" : "INTRO.LANGUAGE" 
                    } 
                }, 
                "filetype" : {  
                    "terms" : {  
                      "field" : "DATA.FILES.FILETYPE" 
                    } 
                }, 
                 "access_right" : {  
                    "terms" : {  
                      "field" : "INTRO.ACCESS_RIGHT" 
                    } 
                } 
            } 
        }';
        if ($query == "null") {
            $url = 'http://localhost/ordar/_search?q=INTRO.FILE_CREATOR.MAIL:' . $author_mail . '%20AND%20(INTRO.FILE_CREATOR.NAME:' . $authors_name . ')&size=10000';
        } else {
            $query = rawurlencode($query);
            $url   = 'http://localhost/ordar/_search?q=' . $query . '%20AND%20(INTRO.FILE_CREATOR.MAIL:' . $author_mail . ')%20AND%20(INTRO.FILE_CREATOR.NAME:' . $authors_name . ')&size=10000';
        }
        $curlopt                    = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => 9200,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postcontent
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, TRUE);
        $responses["hits"]["total"] = $response["hits"]["total"];
        $responses['aggregations']  = $response['aggregations'];
        
        foreach ($response["hits"]["hits"] as $key => $value) {
            $responses["hits"]["hits"][$key]           = $value["_source"]["INTRO"];
            $responses["hits"]["hits"][$key]["_index"] = $value["_index"];
            $responses["hits"]["hits"][$key]["_id"]    = $value["_id"];
            $responses["hits"]["hits"][$key]["_type"]  = $value["_type"];
        }
        ;
        $responses = json_encode($responses);
        return $responses;
    }
    
    
    
    /**
     * Make a request to elasticsearch API for a specific dataset
     * @param doi of dataset
     * @return data of request if find, else false
     */
    function get_info_for_dataset($id)
    {
        $url      = 'http://localhost/ordar/_all/' . urlencode($id);
        $curlopt  = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => 9200,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        );
        $response = self::Curlrequest($url, $curlopt);
        $response = json_decode($response, TRUE);
        
        if ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Open") {
            //$response=json_encode($response);
            return $response;
        } elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Embargoed") {
            $embargoeddate = $response["_source"]["INTRO"]["PUBLICATION_DATE"];
            $now           = new \Datetime();
            
            foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                if (@$_SESSION["mail"] == $value["MAIL"]) {
                    return $response;
                } else {
                    $notfound = "notfound";
                }
            }
            if ($notfound = "notfound") {
                
                $responses["_source"]["INTRO"] = $response["_source"]["INTRO"];
                $responses["_index"]           = $response["_index"];
                $responses["_id"]              = $response["_id"];
                $responses["_type"]            = $response["_type"];
                return $responses;
            }
            
        } elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Closed") {
            foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                if (@$_SESSION["mail"] == $value["MAIL"]) {
                    return $response;
                } else {
                    $notfound = "notfound";
                    
                }
            }
            if ($notfound = "notfound") {
                $responses["_source"]["INTRO"] = $response["_source"]["INTRO"];
                $responses["_index"]           = $response["_index"];
                $responses["_id"]              = $response["_id"];
                $responses["_type"]            = $response["_type"];
                return $responses;
            }
            
            
        } elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Unpublished") {
            $found = "false";
            foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                if (@$_SESSION["mail"] == $value["MAIL"]) {
                    $found = "true";
                }
                
            }
            if ($found == "true") {
                return $response;
            } else {
                return false;
            }
            
            
        }
        
    }
    
    
    /**
     * Send a mail to contact ORDAR owner
     * @param object,message,mail of sender
     * @return true if error, else false
     */
    function Send_Contact_Mail($object, $message, $sendermail)
    {
        
        if (!empty($object) && !empty($message) && filter_var($sendermail, FILTER_VALIDATE_EMAIL)) {
            $mail = mail("<otelo-si@univ-lorraine.fr>", 'Contact from ORDaR :' . $object, " Message from " . $sendermail . ": <br> " . $message, "From:<noreply@ordar.otelo.univ-lorraine.fr>");
            if ($mail == true) {
                $error = "false";
            } else {
                $error = "true";
            }
            return $error;
        } else {
            return $error = "true";
        }
        
        
    }
    
    
}

?>