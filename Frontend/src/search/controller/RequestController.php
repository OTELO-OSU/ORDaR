<?php

namespace search\controller;



class RequestController
{

	function Curlrequest($url,$curlopt){

        $ch = curl_init();
        $curlopt = array(CURLOPT_URL => $url) + $curlopt ;
        curl_setopt_array($ch, $curlopt);
    	$rawData = curl_exec($ch);
	    curl_close($ch);
	    return $rawData;
	}



	
	function requestToAPI($query){
		$query = rawurlencode($query);
		$postcontent='{
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
		              "field" : "INTRO.FILE_CREATOR.NAME"
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
		$url='http://localhost/ordar/_search?q='.$query.'%20AND%20NOT%20INTRO.ACCESS_RIGHT:Unpublished&size=10000';
		$curlopt=array(CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_PORT=> 9200,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 40,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $postcontent);
		$response=self::Curlrequest($url,$curlopt);
		$response=json_decode($response,TRUE);
		$responses["hits"]["total"]=$response["hits"]["total"];
		$responses['aggregations']=$response	['aggregations'];
		foreach ($response["hits"]["hits"] as $key => $value) {
		$responses["hits"]["hits"][$key]=$value["_source"]["INTRO"];
		$responses["hits"]["hits"][$key]["_index"]=$value["_index"];
		$responses["hits"]["hits"][$key]["_id"]=$value["_id"];
		$responses["hits"]["hits"][$key]["_type"]=$value["_type"];
		};
		$responses=json_encode($responses);
		return $responses;
		
	}


	function getPublicationsofUser($author){
		$postcontent='{
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
		              "field" : "INTRO.FILE_CREATOR.NAME"
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
		$url='http://localhost/ordar/_search?q=INTRO.FILE_CREATOR.MAIL:'.$author.'&size=10000';
		$curlopt=array(CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_PORT=> 9200,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 40,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $postcontent);
		$response=self::Curlrequest($url,$curlopt);
		$response=json_decode($response,TRUE);
		$responses["hits"]["total"]=$response["hits"]["total"];
				$responses['aggregations']=$response	['aggregations'];

		foreach ($response["hits"]["hits"] as $key => $value) {
		$responses["hits"]["hits"][$key]=$value["_source"]["INTRO"];
		$responses["hits"]["hits"][$key]["_index"]=$value["_index"];
		$responses["hits"]["hits"][$key]["_id"]=$value["_id"];
		$responses["hits"]["hits"][$key]["_type"]=$value["_type"];
		};
		$responses=json_encode($responses);
		return $responses;
	}


	

	function get_info_for_dataset($id){
		$url='http://localhost/ordar/_all/'.$id;
		$curlopt=array(CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_PORT=> 9200,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 40,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET");
		$response=self::Curlrequest($url,$curlopt);
		$response=json_decode($response,TRUE);

		if ($response["_source"]["INTRO"]["ACCESS_RIGHT"]=="Open") {
			//$response=json_encode($response);
			return $response;
		}
		elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"]=="Embargoed") {
			$embargoeddate=$response["_source"]["INTRO"]["PUBLICATION_DATE"];
			$now= new \Datetime();
			/*if(($embargoeddate <= $now)==false){
				$responses["_source"]["INTRO"]=$response["_source"]["INTRO"];
				$responses["_index"]=$response["_index"];
				$responses["_id"]=$response["_id"];
				$responses["_type"]=$response["_type"];
				//$responses=json_encode($responses);
				return $responses;
			}
			else{
				//$response=json_encode($response);
				return $response;

			}*/
			foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
				if (@$_SESSION["mail"]==$value["MAIL"]) {
					return $response;
				}
				else{
					$notfound="notfound";
				}
				}
				 if($notfound="notfound"){

				$responses["_source"]["INTRO"]=$response["_source"]["INTRO"];
				$responses["_index"]=$response["_index"];
				$responses["_id"]=$response["_id"];
				$responses["_type"]=$response["_type"];
				//$responses=json_encode($responses);
				return $responses;
				}
		
		}
		elseif($response["_source"]["INTRO"]["ACCESS_RIGHT"]=="Closed"){
			foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
					if (@$_SESSION["mail"]==$value["MAIL"]) {
						return $response;
					}
					else{
					$notfound="notfound";
				
				}
				}
				 if($notfound="notfound"){
					

					$responses["_source"]["INTRO"]=$response["_source"]["INTRO"];
					$responses["_index"]=$response["_index"];
					$responses["_id"]=$response["_id"];
					$responses["_type"]=$response["_type"];
					//$responses=json_encode($responses);
					return $responses;
					}


		}

		elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"]=="Unpublished"){
			foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
					if (@$_SESSION["mail"]==$value["MAIL"]) {
						return $response;
					}
					else{
					return false;
				
				}
			}


		}
		
	}



	function Send_Contact_Mail($object,$message,$sendermail){
		$mail=mail("<otelo-si@univ-lorraine.fr>", 'Contact from ORDaR :'.$object, $sendermail." Message from ".$sendermail.": <br> ".$message, ' From:<'.$sendermail.">");

		if ($mail==true) {
			return $error=false;
		}
		else{
			return $error=true;
		}

		}


}

























?>