<?php

namespace search\controller;


use MongoClient;

class DatasheetController
{
    
    /**
     * Create a mongo connection instance
     * @return Mongo_instance
     */
    function connect_tomongo()
    {
        $config = parse_ini_file("config.ini");
        
        if (empty($config['username']) && empty($config['password'])) {
            $this->db = new MongoClient("mongodb://" . $config['host'] . ':' . $config['port']);
        } else {
            $this->db = new MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
                'authSource' => $config['authSource'],
                'username' => $config['username'],
                'password' => $config['password']
            ));
        }
        return $this->db;
    }
    /**
     * Parse Post Data 
     * @param array, post request
     * @return array, parsed data to write
     */
    function Postprocessing($POST)
    {
        $error              = null;
        $author_displayname = null;
        $config             = parse_ini_file("config.ini");
        $UPLOAD_FOLDER      = $config["UPLOAD_FOLDER"];
        $required           = array(
            'title',
            'creation_date',
            'language',
            'authors_name',
            'authors_firstname',
            'authors_email',
            'description',
            'scientific_field',
            'measurement_nature',
            'measurement_abbreviation',
            'measurement_unit',
            'license',
            'publisher',
            'institution'
        );
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $error = "Warning there are empty fields: " . $field;
            }
        }
        
        foreach ($POST as $key => $value) {
            if ($key == "creation_date") {
                $array["CREATION_DATE"] = htmlspecialchars($value, ENT_QUOTES);
            }
            if ($key == "title") {
                $array["TITLE"] = htmlspecialchars($value, ENT_QUOTES);
            }
            if ($key == "language") {
                if ($value == '0') {
                    $language = "FRENCH";
                }
                if ($value == "1") {
                    $language = "ENGLISH";
                }
                $array["LANGUAGE"] = $language;
            }
            if ($key == "sampling_date") {
                if (!count($value == 0)) {
                    if (count($value) > 1) {
                        foreach ($value as $key => $value) {
                            $array["SAMPLING_DATE"][$key] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    } else {
                        $array["SAMPLING_DATE"][0] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "description") {
                $array["DATA_DESCRIPTION"] = htmlspecialchars($value, ENT_QUOTES);
            }
            if ($key == "scientific_field") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $array["SCIENTIFIC_FIELD"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["SCIENTIFIC_FIELD"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "sampling_point_name") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "sampling_point_coordonate_system") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["COORDONATE_SYSTEM"] = htmlspecialchars($value, ENT_QUOTES);
                            ;
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["ABBREVIATION"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "sampling_point_abbreviation") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["ABBREVIATION"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["ABBREVIATION"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
            }
            if ($key == "sampling_point_longitude") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["LONGITUDE"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["LONGITUDE"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "sampling_point_latitude") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["LATITUDE"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["LATITUDE"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "sampling_point_elevation") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["ELEVATION"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["ELEVATION"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "sampling_point_description") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["DESCRIPTION"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["DESCRIPTION"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            
            if ($key == "measurement_nature") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $array["MEASUREMENT"][$key]["NATURE"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["MEASUREMENT"][0]["NATURE"] = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "measurement_abbreviation") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $array["MEASUREMENT"][$key]["ABBREVIATION"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["MEASUREMENT"][0]["ABBREVIATION"] = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "measurement_unit") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $array["MEASUREMENT"][$key]["UNIT"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["MEASUREMENT"][0]["UNIT"] = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "publisher") {
                $array["PUBLISHER"] = htmlspecialchars($value, ENT_QUOTES);
            }
            if ($key == "sample_kind") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLE_KIND"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLE_KIND"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "institution") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["INSTITUTION"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["INSTITUTION"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
            }
            
            if ($key == "authors_firstname") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $array["FILE_CREATOR"][$key]["FIRST_NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        $author_firstname[]                        = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["FILE_CREATOR"][0]["FIRST_NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    $author_firstname                       = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "authors_name") {
                if (count($value) > 1) {
                    foreach ($value as $keys => $value) {
                        $array["FILE_CREATOR"][$keys]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        $author_displayname[]                 = htmlspecialchars($value, ENT_QUOTES) . " " . $author_firstname[$keys];
                        
                    }
                } else {
                    $array["FILE_CREATOR"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    $author_displayname               = htmlspecialchars($value[0], ENT_QUOTES) . " " . $author_firstname;
                }
                if ($author_displayname) {
                    if (is_array($author_displayname)) {
                        foreach ($author_displayname as $key => $value) {
                            $array["FILE_CREATOR"][$key]["DISPLAY_NAME"] = $value;
                        }
                    } else {
                        
                        $array["FILE_CREATOR"][0]["DISPLAY_NAME"] = $author_displayname;
                    }
                }
            }
            if ($key == "authors_email") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $array["FILE_CREATOR"][$key]["MAIL"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["FILE_CREATOR"][0]["MAIL"] = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "keywords") {
                if (count($value <= 3)) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["KEYWORDS"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["KEYWORDS"]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "fundings") {
                if (count($value <= 3)) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["FUNDINGS"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["FUNDINGS"]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                }
                
            }
            
            if ($key == "license") {
                $licensetype = null;
                if ($value == 1) {
                    $licensetype = "Creative commons Attribution alone";
                } elseif ($value == 2) {
                    $licensetype = "Creative commons Attribution + ShareAlike";
                } elseif ($value == 3) {
                    $licensetype = "Creative commons Attribution + Noncommercial";
                } elseif ($value == 4) {
                    $licensetype = "Creative commons Attribution + NoDerivatives";
                } elseif ($value == 5) {
                    $licensetype = "Creative commons Attribution + Noncommercial + ShareAlike";
                } elseif ($value == 6) {
                    $licensetype = "Creative commons Attribution + Noncommercial + NoDerivatives";
                }
                $array["LICENSE"] = $licensetype;
            }
            
            
            if ($key == "access_right") {
                $array["ACCESS_RIGHT"] = $value;
                if ($value == "Closed") {
                    $publication_date = "9999-12-31";
                }
                if ($value == "Open") {
                    $publication_date = date('Y-m-d');
                }
                if ($value == "Embargoed") {
                    $today         = date('Y-m-d');
                    $embargoeddate = $_POST["publication_date"];
                    if ($today < $embargoeddate) {
                        $publication_date = htmlspecialchars($_POST["publication_date"], ENT_QUOTES);
                        ;
                    } else {
                        $error = "Invalid embargo date!";
                    }
                }
                
                
                
                $array["PUBLICATION_DATE"] = $publication_date;
                
                
                
                $array["UPLOAD_DATE"] = date('Y-m-d');
                
            }
        }
        if (!$error == NULL) {
            $array['dataform'] = $array;
            $array['error']    = $error;
            return $array;
        } else {
            return $array;
        }
    }
    
    
    function Newdatasheet($db, $array)
    {
        if ($array['error']) {
            return $array;
        } else {
            $config        = parse_ini_file("config.ini");
            $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
            //$this->db = new MongoClient("mongodb://localhost:27017");
            $doi           = rand(5, 15000);
            for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                $repertoireDestination         = $UPLOAD_FOLDER;
                $nomDestination                = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                $data["FILES"][$i]["DATA_URL"] = $nomDestination;
                if (file_exists($repertoireDestination . $_FILES["file"]["name"][$i])) {
                    $returnarray[] = "false";
                    $returnarray[] = $array;
                    return $returnarray;
                } else {
                    if (is_uploaded_file($_FILES["file"]["tmp_name"][$i])) {
                        if (!file_exists($repertoireDestination . $doi)) {
                            mkdir($repertoireDestination . $doi);
                        }
                        if (rename($_FILES["file"]["tmp_name"][$i], $repertoireDestination . $doi . "/" . $nomDestination)) {
                            $extension = new \SplFileInfo($repertoireDestination . $doi . "/" . $nomDestination);
                            $filetypes = $extension->getExtension();
                            if (strlen($filetypes) == 0 OR strlen($filetypes) > 4) {
                                $filetypes = 'unknow';
                            }
                            $data["FILES"][$i]["FILETYPE"] = $filetypes;
                            $collection                    = "Manual_Depot";
                            $collectionObject              = $this->db->selectCollection($config["authSource"], $collection);
                            $json                          = array(
                                '_id' => $doi,
                                "INTRO" => $array,
                                "DATA" => $data
                            ); //voir pour DOI
                        }
                    }
                }
            }
            
            $collectionObject->insert($json);
            return "true";
        }
    }
    
    
    
    
    
    function Editdatasheet($collection, $doi, $db, $array)
    {
        $config        = parse_ini_file("config.ini");
        $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
        if ($array['error']) {
            return $array;
        } else {
            //$this->db = new MongoClient("mongodb://localhost:27017");
            $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
            if (is_numeric($doi) == true) {
                $doi = $doi;
                
                // Quand les vrai DOI seront implanté il faudra modifier ici
                
                $collectionObject->update(array(
                    '_id' => new \MongoInt32($doi)
                ), array(
                    '$set' => array(
                        "INTRO" => $array
                    )
                ));
            } else {
                $newdoi = rand(5, 15000);
                mkdir($UPLOAD_FOLDER . $newdoi, 0777, true);
                $query  = array(
                    '_id' => $doi
                );
                $cursor = $collectionObject->find($query);
                foreach ($cursor as $key => $value) {
                    $ORIGINAL_DATA_URL = $value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
                }
                unlink($ORIGINAL_DATA_URL);
                //unlink(UPLOAD_FOLDER.$doi.'/'.$doi.'_INTRO.csv')
                //$collectionObject->insert(array('_id' => new \MongoInt32($doi)),array("INTRO" => $array));
                rename($UPLOAD_FOLDER . $doi . '/' . $doi . '_DATA.csv', $UPLOAD_FOLDER . $newdoi . "/" . $doi . '_DATA.csv');
                rmdir($UPLOAD_FOLDER . $doi);
                $collectionObject->update(array(
                    '_id' => $doi
                ), array(
                    '$set' => array(
                        "INTRO" => $array
                    )
                ));
                $olddata = $collectionObject->find(array(
                    '_id' => $doi
                ));
                foreach ($olddata as $key => $value) {
                    $INTRO                                          = $value["INTRO"];
                    $value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"] = NULL;
                    $DATA                                           = $value["DATA"];
                }
                $collectionObject->remove(array(
                    '_id' => $doi
                ));
                $collectionObject->insert(array(
                    '_id' => $newdoi,
                    "INTRO" => $INTRO,
                    "DATA" => $DATA
                ));
            }
            return "true";
        }
    }
    
    
    function removeUnpublishedDatasheet($collection, $doi)
    {
        $config        = parse_ini_file("config.ini");
        $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
        if (is_numeric($doi) == true) {
            // test de suppression si publier
        } else {
            //$this->db = new MongoClient("mongodb://localhost:27017");
            $db               = self::connect_tomongo();
            $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
            $query            = array(
                '_id' => $doi
            );
            $cursor           = $collectionObject->find($query);
            foreach ($cursor as $key => $value) {
                $ORIGINAL_DATA_URL = $value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
            }
            unlink($ORIGINAL_DATA_URL);
            $collectionObject->remove(array(
                '_id' => $doi
            ));
            unlink($UPLOAD_FOLDER . $doi . '/' . $doi . '_DATA.csv');
            rmdir($UPLOAD_FOLDER . $doi);
            print("erase");
        }
        
    }
    
    
    
    
    function Send_Mail_author($doi, $response, $author_name, $author_firstname, $object, $message, $sendermail)
    {
        if (!empty($object) && !empty($message) && filter_var($sendermail, FILTER_VALIDATE_EMAIL)) {
            $title = $response['_source']['INTRO']['TITLE'];
            $headers .= "From:<noreply@ordar.otelo.univ-lorraine.fr>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
                if ($author_name == $value["NAME"] && $author_firstname == $value["FIRST_NAME"]) {
                    $mail = $value["MAIL"];
                    mail("<" . $mail . ">", 'Contact from ORDaR :' . $object, $sendermail . " want to know something about this dataset: <br>DOI: " . $doi . "<br>Title: " . $title . " <br> Message from " . $sendermail . ": <br> " . $message, $headers);
                }
            }
            if ($mail == true) {
                return $error = "false";
            } else {
                return $error = "true";
            }
        } else {
            return $error = "true";
        }
        
    }
}

?>