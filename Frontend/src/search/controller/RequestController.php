<?php
namespace search\controller;
ini_set('memory_limit', '-1');
class RequestController
{
    /**
     * Make a curl request
     * @param url to request,option
     * @return data of request
     */
    private function Curlrequest($url, $curlopt)
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
    function Check_status_datacite()
    {
        $handle = curl_init("https://mds.datacite.org");
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        return $httpCode;
    }
    function Check_if_DOI_exist()
    {
        $config     = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $dbdoi      = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['DOI_database'],
            'username' => $config['user_doi'],
            'password' => $config['password_doi']
        ));
        $collection = $dbdoi->selectCollection($config['DOI_database'], "DOI");
        if ($collection->count() == 1) {
            $query  = array(
                'STATE' => 'UNLOCKED'
            );
            $cursor = $collection->find($query);
            $count  = $cursor->count();
            if ($count == 1) {
                foreach ($cursor as $key => $value) {
                    $DOI    = $value['ID'];
                    $NewDOI = ++$DOI;
                }
            }
        }
        $url     = "https://mds.datacite.org/metadata/" . $config['DOI_PREFIX'] . "/ORDAR-" . $NewDOI;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml'
            )
        );
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $DOI_exist = curl_exec($ch);
        $info      = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            foreach ($config["admin"] as $key => $value) {
                $array = explode(",", $value);
            }
            foreach ($array as $key => $value) {
                $headers .= "From:<noreply@ordar.otelo.univ-lorraine.fr>\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                $mail = mail($value, 'Error in ORDaR ', '<html>
                <body>
                    <h2>Error occured in ordar!</h2>
                    <p>This DOI ORDAR-' . $NewDOI . ' is already registred check your database DOI.<p>
                </body>
                </html> ', $headers);
            }
            return true;
        } else {
            return false;
        }
    }
    function Inactivate_doi($doi)
    {
        $config  = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $url     = "https://mds.datacite.org/metadata/" . $doi;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml'
            )
        );
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $rawData = curl_exec($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);
    }
    function requestToAPIAdmin($query)
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        if (!empty($query)) {
            $query = rawurlencode($query);
        } else {
            $query = "*";
        }
        $postcontent                = '{  
            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} , 
             "_source": { 
            "excludes": [ "DATA" ] 
             }, 
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
        $bdd                        = strtolower($config['authSource']);
        $url                        = 'http://localhost/' . $bdd . '/_search?q=' . $query . '&size=10000';
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
     * Make a request to elasticsearch API
     * @param query of user
     * @return data of request
     */
    function requestToAPI($query)
    {
        $config                     = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $query                      = rawurlencode($query);
        $postcontent                = '{ 

            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} , 
            "_source": { 
            "excludes": [ "DATA" ] 
             }, 
             
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
        $bdd                        = strtolower($config['authSource']);
        $url                        = 'http://localhost/' . $bdd . '/_search?q=' . $query . '%20AND%20NOT%20INTRO.ACCESS_RIGHT:Unpublished%20AND%20NOT%20INTRO.ACCESS_RIGHT:Draft&size=10000';
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
    function send_XML_to_datacite($XML, $doi)
    {
        $config  = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $url     = "https://mds.datacite.org/metadata/" . $doi;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $XML,
            CURLOPT_HTTPHEADER => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml'
            )
        );
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $XMLondatacite = curl_exec($ch);
        curl_close($ch);
        if ($XMLondatacite == $XML) {
            return "true";
        } else {
            $url     = "https://mds.datacite.org/metadata/";
            $curlopt = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $XML,
                CURLOPT_HTTPHEADER => array(
                    "authorization: " . $config['Auth_config_datacite'],
                    'Content-Type: text/xml'
                )
            );
            $ch      = curl_init();
            $curlopt = array(
                CURLOPT_URL => $url
            ) + $curlopt;
            curl_setopt_array($ch, $curlopt);
            $rawData = curl_exec($ch);
            $info    = curl_getinfo($ch);
            curl_close($ch);
            if ($info['http_code'] == "201") {
                $url_doi = urlencode($config['URL_DOI'] . "/record?id=" . $doi);
                $curl    = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://mds.datacite.org/doi",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "doi=" . $doi . "&url=" . $url_doi,
                    CURLOPT_HTTPHEADER => array(
                        "authorization: " . $config['Auth_config_datacite'],
                        "cache-control: no-cache"
                    )
                ));
                $response = curl_exec($curl);
                return "true";
            } else {
                return "false";
            }
        }
    }
    /**
     * Make a request to elasticsearch API by user
     * @param mail of authors,name of auhtors, query of user
     * @return data of request
     */
    function getPublicationsofUser($author_mail, $authors_name, $query)
    {
        $config      = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $postcontent = '{  
            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} , 
            "_source": { 
            "excludes": [ "DATA" ] 
             }, 
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
        $bdd         = strtolower($config['authSource']);
        if ($query == "null") {
            $url = 'http://localhost/' . $bdd . '/_search?q=INTRO.FILE_CREATOR.MAIL:' . $author_mail . '%20AND%20(INTRO.FILE_CREATOR.NAME:' . $authors_name . ')&size=10000';
        } else {
            $query = rawurlencode($query);
            $url   = 'http://localhost/' . $bdd . '/_search?q=' . $query . '%20AND%20(INTRO.FILE_CREATOR.MAIL:' . $author_mail . ')%20AND%20(INTRO.FILE_CREATOR.NAME:' . $authors_name . ')&size=10000';
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
    function get_info_for_dataset($id, $restricted)
    {
        $config   = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $bdd      = strtolower($config['authSource']);
        $url      = 'http://localhost/' . $bdd . '/_all/' . urlencode($id);
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
        if ($restricted == "Unrestricted") {
            return $response;
        } else {
            if ($_SESSION['admin'] == "1") {
                if ($response['found'] == false) {
                    return false;
                } else {
                    return $response;
                }
            } else {
                if ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Open") {
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
                elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Draft") {
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
            $headers .= "From:<noreply@ordar.otelo.univ-lorraine.fr>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            $mail = mail("<otelo-si@univ-lorraine.fr>", 'Contact from ORDaR : ' . $object, '<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
         <h2>Contact from :  <img src="https://ordar.otelo.univ-lorraine.fr/img/ordar_logo.png" alt="Logo ordar" height="30" width="120" /> </h2>  
        <table cellspacing="0" style="border: 2px solid black; width: 400px; height: 200px;">
            <tr>
                <th>From:</th><td>' . $sendermail . '</td>
            </tr>
            <tr style="background-color: #e0e0e0;">
                <th>Subject:</th><td>' . $object . '</td>
            </tr>
            <tr>
                <th valign="bottom">Message:</th><td>' . $message . '</td>
            </tr>
        </table>
    </body>
    </html> ', $headers);
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
    /**
     * Send a mail when upload successfull
     * @return true if error, else false
     */
    function Send_Mail_To_uploader($authors,$title, $doi, $description)
    {
        $headers = "From:<noreply@ordar.otelo.univ-lorraine.fr>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        foreach ($authors as $key => $value) {
        $mail = mail($value['MAIL'], 'Dataset submit successfully! ', '<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <h2>Your dataset is now on <img src="https://ordar.otelo.univ-lorraine.fr/img/ordar_logo.png" alt="Logo ordar" height="30" width="120" /></h2>
        <p> '.$_SESSION['name'].' '.$_SESSION['firstname'].' has published this dataset </p>
        <p> Your DOI is : <a href="http://dx.doi.org/' . $doi . '">' . $doi . '</a></p>
         <table cellspacing="0" style="border: 2px solid black; width: 500px; height: 200px;">
            <tr>
                <th>Title : </th><td>' . $title . '</td>
            </tr>
             <tr style="background-color: #e0e0e0;">
                <th>Description : </th><td>' . $description . '</td>
            </tr>
        </table>
    </body>
    </html> ', $headers); 
        if ($mail == true) {
            $error = "false";
        } else {
            $error = "true";
        }
        }
        return $error;
    }
}
?>