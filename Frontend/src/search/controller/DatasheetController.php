<?php

namespace search\controller;
use \search\controller\RequestController as RequestApi;
use MongoClient;

class DatasheetController
{
    
    /**
     * Create a mongo connection instance
     * @return Mongo_instance
     */
    function connect_tomongo()
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        
        
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
     * Generate a new DOI
     * @return a new doi if success 
     */
    
    function generateDOI()
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        
        $dbdoi      = new MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
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
                    $update = $collection->update(array(
                        "_id" => $value['_id']
                    ), array(
                        '$set' => array(
                            "STATE" => "LOCKED"
                        )
                    ));
                    $DOI    = $value['ID'];
                    $NewDOI = ++$DOI;
                    $update = $collection->update(array(
                        "_id" => $value['_id']
                    ), array(
                        '$set' => array(
                            "ID" => $NewDOI
                        )
                    ));
                    $update = $collection->update(array(
                        "_id" => $value['_id']
                    ), array(
                        '$set' => array(
                            "STATE" => "UNLOCKED"
                        )
                    ));
                }
                return $NewDOI;
            } else {
                return false;
            }
        } else {
            $cursor = $collection->insert(array(
                '_id' => "ORDAR-DOI",
                'ID' => 0,
                'STATE' => "UNLOCKED"
            ));
            
            $query  = array(
                'STATE' => 'UNLOCKED'
            );
            $cursor = $collection->find($query);
            $count  = $cursor->count();
            if ($count == 1) {
                foreach ($cursor as $key => $value) {
                    $update = $collection->update(array(
                        "_id" => $value['_id']
                    ), array(
                        '$set' => array(
                            "STATE" => "LOCKED"
                        )
                    ));
                    $DOI    = $value['ID'];
                    $NewDOI = ++$DOI;
                    $update = $collection->update(array(
                        "_id" => $value['_id']
                    ), array(
                        '$set' => array(
                            "ID" => $NewDOI
                        )
                    ));
                    $update = $collection->update(array(
                        "_id" => $value['_id']
                    ), array(
                        '$set' => array(
                            "STATE" => "UNLOCKED"
                        )
                    ));
                }
                return $NewDOI;
            }
        }
    }
    
    
    /**
     * Parse Post Data 
     * @param array, post request
     * @return array, parsed data to write
     */
    function Postprocessing($POST, $method, $doi)
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        
        $sxe = new \SimpleXMLElement("<resource/>");
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xmlns', 'http://datacite.org/schema/kernel-4');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4/metadata.xsd');
        
        $creators        = $sxe->addChild('creators');
        $publicationYear = $sxe->addChild('publicationYear', date('Y'));
        $subjects        = $sxe->addChild('subjects');
        $titles          = $sxe->addChild('titles');
        $RessourceType   = $sxe->addChild('resourceType', 'Dataset');
        $RessourceType->addAttribute('resourceTypeGeneral', 'Dataset');
        $Version      = $sxe->addChild('version', '1');
        $descriptions = $sxe->addChild('descriptions');
        
        
        
        $error              = null;
        $author_displayname = null;
        $fields             = null;
        $UPLOAD_FOLDER      = $config["UPLOAD_FOLDER"];
        $required           = array(
            'title',
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
            'institution',
            'access_right'
        );

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $fields[]=$field;
            }
        }
        if (count($fields)!=0) {
            $txt=null;
            foreach ($fields as $key => $value) {
                $txt.="  ".$value;
            }
            $error = "Warning there are empty fields: " . $txt;
        }

        foreach ($POST as $key => $value) {
            
            if ($key == "title") {
                $array["TITLE"] = htmlspecialchars($value, ENT_QUOTES);
                $title          = $titles->addChild('title', htmlspecialchars($value, ENT_QUOTES));
            }
            if ($key == "language") {
                if ($value == '2') {
                    $language = "FRENCH";
                }
                if ($value == "1") {
                    $language = "ENGLISH";
                }
                $array["LANGUAGE"] = $language;
                $sxe->addChild('language', $language);
            }
            if ($key == "sampling_date") {
                if ($value[0] == "") {
                } else {
                    if (count($value) > 1) {
                        if (count(array_unique($value)) < count($value)) {
                            $error = "Sampling date must be unique";
                            foreach ($value as $key => $value) {
                                if (\DateTime::createFromFormat('Y-m-d', $value) !== FALSE) {
                                    $array["SAMPLING_DATE"][$key] = htmlspecialchars($value, ENT_QUOTES);
                                }
                                else{
                                    $error="Sampling date invalid";
                                }
                            }
                        }
                        
                        else {
                            foreach ($value as $key => $value) {
                               if (\DateTime::createFromFormat('Y-m-d', $value) !== FALSE) {
                                    $array["SAMPLING_DATE"][$key] = htmlspecialchars($value, ENT_QUOTES);
                                }
                                else{
                                    $error="Sampling date invalid";
                                }
                            }
                        }
                    } else {
                        if (\DateTime::createFromFormat('Y-m-d', $value[0]) !== FALSE) {
                                    $array["SAMPLING_DATE"][0] = htmlspecialchars($value[0], ENT_QUOTES);
                                }
                                else{
                                    $error="Sampling date invalid";
                                }
                    }
                }
                
            }
            if ($key == "description") {
                $array["DATA_DESCRIPTION"] = htmlspecialchars($value, ENT_QUOTES);
                $description               = $descriptions->addChild('description', htmlspecialchars($value, ENT_QUOTES));
                $description->addAttribute('descriptionType', 'Abstract');
                
                
            }
            if ($key == "scientific_field") {
                if (count($value) > 1) {
                    $x = 0;
                    foreach ($value as $key => $value) {
                        $x++;
                        if ($x > 3) {
                            break;
                        } else {
                            $array["SCIENTIFIC_FIELD"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                            $subjects->addChild('subject', htmlspecialchars($value, ENT_QUOTES));
                        }
                    }
                } else {
                    $array["SCIENTIFIC_FIELD"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    $subjects->addChild('subject', htmlspecialchars($value[0], ENT_QUOTES));
                }
            }
            if ($key == "sampling_point_name") {
                if (count($value) > 1) {
                    if (count(array_unique($value)) < count($value)) {
                        $error = "Sample name must be unique";
                        foreach ($value as $key => $value) {
                            if (!empty($value)) {
                                $array["SAMPLING_POINT"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                            }
                        }
                    } else {
                        foreach ($value as $key => $value) {
                            if (!empty($value)) {
                                $array["SAMPLING_POINT"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                            }
                        }
                        
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "sampling_point_coordinate_system") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        if (!empty($value)) {
                            $array["SAMPLING_POINT"][$key]["COORDINATE_SYSTEM"] = htmlspecialchars($value, ENT_QUOTES);
                            ;
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["SAMPLING_POINT"][0]["COORDINATE_SYSTEM"] = htmlspecialchars($value[0], ENT_QUOTES);
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
                $publisher          = $sxe->addChild('publisher', htmlspecialchars($value, ENT_QUOTES));
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
                        if (empty($value)) {
                            $error = "Warning there are empty fields: author ";
                        }
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
                        if (empty($value)) {
                            $error = "Warning there are empty fields: author  ";
                        }
                        $array["FILE_CREATOR"][$keys]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                        $author_displayname[]                 = htmlspecialchars($value, ENT_QUOTES) . " " . $author_firstname[$keys];
                        $creator                              = $creators->addChild('creator');
                        $creator->addChild('creatorName', htmlspecialchars($value, ENT_QUOTES) . " " . $author_firstname[$keys]);
                        
                    }
                } else {
                    $array["FILE_CREATOR"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    $author_displayname               = htmlspecialchars($value[0], ENT_QUOTES) . " " . $author_firstname;
                    $creator                          = $creators->addChild('creator');
                    $creator->addChild('creatorName', htmlspecialchars($value[0], ENT_QUOTES) . " " . $author_firstname);
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
                        if (empty($value)) {
                            $error = "Warning there are empty fields: author ";
                        }
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $error = "Warning author mail invalid ";
                        }
                        
                        $array["FILE_CREATOR"][$key]["MAIL"] = htmlspecialchars($value, ENT_QUOTES);
                    }
                } else {
                    $array["FILE_CREATOR"][0]["MAIL"] = htmlspecialchars($value[0], ENT_QUOTES);
                }
            }
            if ($key == "keywords") {
                if (count($value) > 1) {
                    if (count($value <= 3)) {
                        $x = 0;
                        foreach ($value as $key => $value) {
                            $x++;
                            if ($x > 3) {
                                break;
                            } else {
                                if (!empty($value)) {
                                    $array["KEYWORDS"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                                }
                            }
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["KEYWORDS"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
                    }
                }
                
            }
            if ($key == "fundings") {
                if (count($value) > 1) {
                    if (count($value <= 3)) {
                        $x = 0;
                        foreach ($value as $key => $value) {
                            $x++;
                            if ($x > 3) {
                                break;
                            } else {
                                if (!empty($value)) {
                                    $array["FUNDINGS"][$key]["NAME"] = htmlspecialchars($value, ENT_QUOTES);
                                }
                            }
                        }
                    }
                } else {
                    if (!empty($value[0])) {
                        $array["FUNDINGS"][0]["NAME"] = htmlspecialchars($value[0], ENT_QUOTES);
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
                
                $array["METADATA_DATE"] = date("Y-m-d");
                
                $array["PUBLICATION_DATE"] = $publication_date;
                
                
                
                
            }
            if ($key == "file_already_uploaded") {
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $file_already_uploaded[$key]['DATA_URL'] = $value;
                    }
                } else {
                    $file_already_uploaded[0]['DATA_URL'] = $value[0];
                }
            }
            
        }
        
        
        if (!$error == NULL) {
            $array['dataform'] = $array;
            $array['error']    = $error;
            return $array;
        } else {
            if ($method == "Edit") {
                $doi               = $doi;
                $array['dataform'] = $array;
                if (empty($file_already_uploaded)) {
                    $array['file_already_uploaded'] = array();
                } else {
                    $array['file_already_uploaded'] = $file_already_uploaded;
                }
                $array['xml'] = $sxe;
                $array['doi'] = $doi;
                return $array;
            } else {
                $newdoi = self::generateDOI();
                if ($newdoi != false) {
                    $doi        = $config["DOI_PREFIX"] . "/" . "ORDAR-" . $newdoi;
                    $identifier = $sxe->addChild('identifier', $doi);
                    $identifier->addAttribute('identifierType', 'DOI');
                    $array['dataform'] = $array;
                    $array['xml']      = $sxe;
                    $array['doi']      = $doi;
                    
                    return $array;
                } else {
                    $array['dataform'] = $array;
                    $array['error']    = "Fail to generate DOI please try again!";
                    return $array;
                }
            }
            
        }
    }
    
    /**
     * Create new datasheet
     * @param mongo connection object, array of POST data 
     * @return true if insert is ok else array of data 
     */
    
    function Newdatasheet($db, $array)
    {
        if (isset($array['error'])) { //Si une erreur est detecté
            return $array;
        } else {
            $config                             = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
            $array['dataform']["UPLOAD_DATE"]   = date('Y-m-d');
            $array['dataform']["CREATION_DATE"] = date('Y-m-d');
            $UPLOAD_FOLDER                      = $config["UPLOAD_FOLDER"];
            $doi                                = $array['doi'];
            for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                $repertoireDestination                  = $UPLOAD_FOLDER;
                $nomDestination                         = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                $data["FILES"][$i]["DATA_URL"]          = $nomDestination;
                $data["FILES"][$i]["ORIGINAL_DATA_URL"] = $UPLOAD_FOLDER . "/" . $doi . "/" . $nomDestination;
                if (file_exists($repertoireDestination . $_FILES["file"]["name"][$i])) {
                    $returnarray[] = "false";
                    $returnarray[] = $array['dataform'];
                    return $returnarray;
                } else {
                    if (is_uploaded_file($_FILES["file"]["tmp_name"][$i])) {
                        if (is_dir($repertoireDestination . $config['DOI_PREFIX']) == false) {
                            mkdir($repertoireDestination . $config['DOI_PREFIX']);
                        }
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
                                "INTRO" => $array['dataform'],
                                "DATA" => $data
                            );
                        } else {
                            $returnarray[] = "false";
                            $returnarray[] = $array['dataform'];
                            return $returnarray;
                        }
                    }
                }
            }
            
            $Request = new RequestApi();
            $request = $Request->send_XML_to_datacite($array['xml']->asXML(), $doi);
            if ($request == "true") {
                $collectionObject->insert($json);
                $Request->Send_Mail_To_uploader($array['dataform']['TITLE'], $doi, $array['dataform']['DATA_DESCRIPTION']);
                return "true";
            } else {
                $array['error'] = "Unable to send metadata to Datacite";
                return $array;
            }
        }
    }
    
    
    
    
    /**
     * Edit datasheet
     * @param collection to edit, doi of dataset to edit,mongo connection object, array of POST data 
     * @return true if insert is ok else array of data 
     */
    
    function Editdatasheet($collection, $doi, $db, $array)
    {
        $config           = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $UPLOAD_FOLDER    = $config["UPLOAD_FOLDER"];
        $collectionObject = $db->selectCollection($config["authSource"], $collection);
        $query            = array(
            '_id' => $doi
        );
        $cursor           = $collectionObject->find($query);
        foreach ($cursor as $key => $value) {
            if ($value['INTRO']["UPLOAD_DATE"]) {
                $array['dataform']['UPLOAD_DATE'] = $value['INTRO']['UPLOAD_DATE'];
            }
            if ($value['INTRO']["CREATION_DATE"]) {
                $array['dataform']['CREATION_DATE'] = $value['INTRO']['CREATION_DATE'];
            }
        }
        
        if (isset($array['error'])) { //Si une erreur est detecté
            return $array;
        } else {
            $collectionObject = $db->selectCollection($config["authSource"], $collection);
            if (strstr($doi, 'ORDAR') !== FALSE) { //Si un DOI perrene est assigné
                
                if ($_SESSION['admin'] == 1) {
                    $query    = array(
                        '_id' => $doi
                    );
                    $cursor   = $collectionObject->find($query);
                    $tmparray = array();
                    foreach ($cursor as $key => $value) {
                        foreach ($value["DATA"]["FILES"] as $key => $value) {
                            $tmparray[] = $value;
                        }
                    }
                    
                    $intersect = array();
                    foreach ($tmparray as $key => $value) {
                        foreach ($array['file_already_uploaded'] as $key => $value2) {
                            if ($value['DATA_URL'] == $value2['DATA_URL']) {
                                $intersect[] = $value;
                            }
                        }
                    }
                    
                    for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                        $repertoireDestination         = $UPLOAD_FOLDER;
                        $nomDestination                = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                        $data[$i]["DATA_URL"]          = $nomDestination;
                        $data[$i]["ORIGINAL_DATA_URL"] = $UPLOAD_FOLDER . "/" . $doi . "/" . $nomDestination;
                        
                        if (is_uploaded_file($_FILES["file"]["tmp_name"][$i])) {
                            if (is_dir($repertoireDestination . $config['DOI_PREFIX']) == false) {
                                mkdir($repertoireDestination . $config['DOI_PREFIX']);
                            }
                            if (!file_exists($repertoireDestination . $doi)) {
                                mkdir($repertoireDestination . $doi);
                            }
                            if (rename($_FILES["file"]["tmp_name"][$i], $repertoireDestination . $doi . "/" . $nomDestination)) {
                                $extension = new \SplFileInfo($repertoireDestination . $doi . "/" . $nomDestination);
                                $filetypes = $extension->getExtension();
                                if (strlen($filetypes) == 0 OR strlen($filetypes) > 4) {
                                    $filetypes = 'unknow';
                                }
                                $data[$i]["FILETYPE"] = $filetypes;
                                $collectionObject     = $this->db->selectCollection($config["authSource"], $collection);
                            } else {
                                $returnarray[] = "false";
                                $returnarray[] = $array['dataform'];
                                return $returnarray;
                            }
                        }
                        
                    }
                    
                    if (count($intersect) != 0 and $data != 0) {
                        $merge = array_merge($intersect, $data);
                    } else if (count($intersect) != 0) {
                        $merge = $intersect;
                        
                    } else {
                        $merge = $data;
                        
                    }
                    
                    $merge = array_map("unserialize", array_unique(array_map("serialize", $merge)));
                    mkdir($UPLOAD_FOLDER . "/" . $doi . "/tmp");
                    foreach ($merge as $key => $value) {
                        rename($UPLOAD_FOLDER . "/" . $doi . "/" . $value['DATA_URL'], $UPLOAD_FOLDER . "/" . $doi . "/tmp/" . $value['DATA_URL']);
                    }
                    $files = glob($UPLOAD_FOLDER . "/" . $doi . "/*"); // get all file names
                    foreach ($files as $file) { // iterate files
                        if (is_file($file))
                            unlink($file); // delete file
                    }
                    foreach ($merge as $key => $value) {
                        rename($UPLOAD_FOLDER . "/" . $doi . "/tmp/" . $value['DATA_URL'], $UPLOAD_FOLDER . "/" . $doi . "/" . $value['DATA_URL']);
                    }
                    rmdir($UPLOAD_FOLDER . "/" . $doi . "/tmp/");
                    
                    
                    $json = array(
                        '$set' => array(
                            "INTRO" => $array['dataform'],
                            "DATA.FILES" => $merge
                        )
                    );
                } else {
                    $json = array(
                        '$set' => array(
                            "INTRO" => $array['dataform']
                        )
                    );
                }
                $doi        = $doi;
                $Request    = new RequestApi();
                $xml        = $array['xml'];
                $identifier = $xml->addChild('identifier', $doi);
                $identifier->addAttribute('identifierType', 'DOI');
                $request = $Request->send_XML_to_datacite($xml->asXML(), $doi);
                if ($request == "true") { //Si les donnnées ont bien été receptionné par datacite
                    $collectionObject->update(array(
                        '_id' => $doi
                    ), $json);
                    return "true";
                } else {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
            } else {
                $newdoi = "ORDAR-" . self::generateDOI();
                
                $Request    = new RequestApi();
                $xml        = $array['xml'];
                $identifier = $xml->addChild('identifier', $config["DOI_PREFIX"] . "/" . $newdoi);
                $identifier->addAttribute('identifierType', 'DOI');
                $request = $Request->send_XML_to_datacite($xml->asXML(), $config["DOI_PREFIX"] . "/" . $newdoi);
                if ($request == "true") {
                    
                    mkdir($UPLOAD_FOLDER . "/" . $config["DOI_PREFIX"] . "/" . $newdoi, 0777, true);
                    $query  = array(
                        '_id' => $doi
                    );
                    $cursor = $collectionObject->find($query);
                    foreach ($cursor as $key => $value) {
                        $ORIGINAL_DATA_URL = $value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"];
                    }
                    unlink($ORIGINAL_DATA_URL);
                    rename($UPLOAD_FOLDER . $doi . '/' . $doi . '_DATA.csv', $UPLOAD_FOLDER . "/" . $config["DOI_PREFIX"] . "/" . $newdoi . "/" . $doi . '_DATA.csv');
                    rmdir($UPLOAD_FOLDER . $doi);
                    $collectionObject->update(array(
                        '_id' => $doi
                    ), array(
                        '$set' => array(
                            "INTRO" => $array['dataform']
                        )
                    ));
                    $olddata = $collectionObject->find(array(
                        '_id' => $doi
                    ));
                    foreach ($olddata as $key => $value) {
                        $INTRO                                          = $value["INTRO"];
                        $value["DATA"]["FILES"][0]["ORIGINAL_DATA_URL"] = $UPLOAD_FOLDER . "/" . $config["DOI_PREFIX"] . "/" . $newdoi . "/" . $value["DATA"]["FILES"][0]["DATA_URL"];
                        $DATA                                           = $value["DATA"];
                    }
                    $collectionObject->remove(array(
                        '_id' => $doi
                    ));
                    $collectionObject->insert(array(
                        '_id' => $config["DOI_PREFIX"] . "/" . $newdoi,
                        "INTRO" => $INTRO,
                        "DATA" => $DATA
                    ));
                    $Request->Send_Mail_To_uploader($array['dataform']['TITLE'], $config["DOI_PREFIX"] . "/" . $newdoi, $array['dataform']['DATA_DESCRIPTION']);
                    
                } else {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
                
            }
            
        }
    }
    
    
    /**
     * Remove datasheet
     * @param collection to edit, doi of dataset to edit 
     * @return true if remove is ok else false
     */
    function removeUnpublishedDatasheet($collection, $doi)
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        
        $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
        if ($collection == null) {
            return false;
        }
        if (strstr($doi, 'ORDAR') !== FALSE) {
            if ($_SESSION['admin'] == 1) {
                $db               = self::connect_tomongo();
                $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
                $query            = array(
                    '_id' => $doi
                );
                $cursor           = $collectionObject->find($query);
                foreach ($cursor as $key => $value) {
                    foreach ($value["DATA"]["FILES"] as $key => $value) {
                        $ORIGINAL_DATA_URL = $value["ORIGINAL_DATA_URL"];
                        unlink($ORIGINAL_DATA_URL);
                    }
                }
                $collectionObject->remove(array(
                    '_id' => $doi
                ));
                unlink($UPLOAD_FOLDER . $doi . '/' . $doi . '_DATA.csv');
                rmdir($UPLOAD_FOLDER . $doi);
                $request = new RequestApi();
                $request->Inactivate_doi($doi);
                
                return true;
            } else {
                
                return false;
            }
        } else {
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
            return true;
        }
        
    }
    
    
    /**
     * Send a mail to author of a dataset
     * @param  doi of dataset , data of dataset,nom de l'auteur,prenom de l'auteur,object du mail,message, mail de l'expediteur
     * @return true if error else false
     */
    
    function Send_Mail_author($doi, $response, $author_name, $author_firstname, $object, $message, $sendermail)
    {
        if (!empty($object) && !empty($message) && filter_var($sendermail, FILTER_VALIDATE_EMAIL)) {
            $title = $response['_source']['INTRO']['TITLE'];
            $headers .= "From:<noreply@ordar.otelo.univ-lorraine.fr>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
                if ($author_name == $value["NAME"] && $author_firstname == $value["FIRST_NAME"]) {
                    $mail = $value["MAIL"];
                    mail("<" . $mail . ">", 'Contact from ORDaR : ' . $object, '<html> 
                            <head> 
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
                            </head> 
                            <body> 
                                <h2>Contact from :  <img src="https://ordar.otelo.univ-lorraine.fr/img/ordar_logo.png" alt="Logo ordar" height="30" width="120" /> </h2>  
                                <table cellspacing="0" style="border: 2px solid black; min-width: 300px; width: auto; height: 200px;  "> 
                                    <tr> 
                                        <th>Title</th><td>' . $title . '</td> 
                                    </tr> 
                                     <tr style="background-color: #e0e0e0;"> 
                                        <th>DOI </th><td><a href="http://dx.doi.org/' . $doi . '">' . $doi . '</a></td> 
                                    </tr> 
                                   <tr></tr> 
                                    <br> 
                                    <tr> 
                                        <th>From:</th><td>' . $sendermail . '</td> 
                                    </tr> 
                                    <tr style="background-color: #e0e0e0;"> 
                                        <th>Subject:</th><td>' . $object . '</td> 
                                    </tr> 
                                    <tr> 
                                        <th valign="top">Message: </th><td>' . nl2br($message) . '</td> 
                                    </tr> 

                                </table> 
                            </body> 
                        </html> ', $headers);
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