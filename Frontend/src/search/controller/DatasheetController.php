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
        if ($collection->count() == 1) {//Verification du statut de la variable DOI (si UNLOCKED on peut y acceder)                
            $maxTries = 3;
            for ($try=1; $try<=$maxTries; $try++) {
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
                    }
                    $result=$NewDOI;
                    break;
                } else {
                    $result=false;
                }
                sleep(3);
                
            }
            return $result;
        } else {
            $cursor = $collection->insert(array(
                '_id' => $config['REPOSITORY_NAME']."-DOI",
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
                }
                return $NewDOI;
            }
        }
    }
    
    function Check_Document($collection,$db,$doi){
        $error=true;
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
        $query            = array(
                '_id' => $doi
            );
        $cursor           = $collectionObject->find($query);
        if ($cursor->count()==0) {
            $error= false;
        }
        return $error;
    }    

    function Increment_DOI(){
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
    
        $dbdoi      = new MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['DOI_database'],
            'username' => $config['user_doi'],
            'password' => $config['password_doi']
        ));
        $collection = $dbdoi->selectCollection($config['DOI_database'], "DOI");
        $query  = array(
                'STATE' => 'LOCKED'
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
                    $DOI    = ++$value['ID'];
                   
                }
            }
            $update = $collection->update(array(
                    "_id" => $config['REPOSITORY_NAME']."-DOI"
                ), array(
                    '$set' => array(
                        "ID" => $DOI
                    )
                ));
            self::UnlockDOI();
            
    }

    function UnlockDOI(){
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
    
        $dbdoi      = new MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['DOI_database'],
            'username' => $config['user_doi'],
            'password' => $config['password_doi']
        ));
        $collection = $dbdoi->selectCollection($config['DOI_database'], "DOI");
        $update = $collection->update(array(
                    "_id" => $config['REPOSITORY_NAME']."-DOI"
                ), array(
                    '$set' => array(
                        "STATE" => "UNLOCKED"
                    )
                ));
    }
    
    function Postprocessing($POST, $method, $doi, $db, $collection)
    {
        
        if (array_key_exists('save', $POST)) {//si c'est une creation de draft
            $access_right     = null;
            $array            = self::Postprocessing_publish($POST, $method, $doi, "Draft");
            $config           = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
            $query            = array(
                '_id' => $doi
            );
            $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
            $cursor           = $collectionObject->find($query);
            foreach ($cursor as $key => $value) {
                $access_right = $value['INTRO']['ACCESS_RIGHT'];
            }
            if ($access_right == "Draft") {//verification que c'est un draft si créé
                return self::ManageDraft($db, $array);
            } elseif ($cursor->count() == 0) {//Creation d'un nouveau draft
                return self::ManageDraft($db, $array);
            }
        }
        if (array_key_exists('publish', $POST)) {//Si on publie le jeu de données
            $array = self::Postprocessing_publish($POST, $method, $doi, "Publish");
            if ($method == "Edit") {//Si c'est une edition
                return self::Editdatasheet($collection, $doi, $db, $array);
            }
             elseif ($method == "Upload"){//Si c'est un nouveau jeu de données
                return self::Newdatasheet($db, $array);
            }
        }
        
    }
    
    
    
    
    /**
     * Parse Post Data 
     * @param array, post request
     * @return array, parsed data to write
     */
    function Postprocessing_publish($POST, $method, $doi, $type)
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $sxe = new \SimpleXMLElement("<resource/>");//Intitalisation object XML
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');//Ajout attribut XML
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
        
        $error              = null;//Declaration
        $author_displayname = null;
        $fields             = null;
        $UPLOAD_FOLDER      = $config["UPLOAD_FOLDER"];

        if ($type == "Draft") {// Champs obligatoire Si c'est un Draft qui est traité
            $required = array(
                'title',
                'language',
                'authors_name',
                'authors_firstname',
                'authors_email'
            );
        } elseif($type == "Publish") { //  Champs obligatoire Si on publie
            $required = array(
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
        }
        
        foreach ($required as $field) { //Verif des champs à traiter
            if (empty($_POST[$field])or empty($_POST[$field][0])or empty($_POST[$field][0][0])) {
                $fields[] = $field;
            }
            
        }
        if (count($fields) != 0) {//Affichage des champs manquants 
            $txt = null;
            foreach ($fields as $key => $value) {
                $txt .= "  " . $value;
            }
            $error = "Warning there are empty fields: " . $txt;
        }
        
        foreach ($POST as $key => $value) {//On parcourt le tableau POST de données
            //HTML specialchars permet de sécuriser les inputs de l'utilisateur pour se proteger des failles XSS
            if ($key == "title") {//Traitement du titre
                $array["TITLE"] = htmlspecialchars($value, ENT_QUOTES);
                $title          = $titles->addChild('title', htmlspecialchars($value, ENT_QUOTES));
            }
            if ($key == "language") {//Traitement de la langue
                if ($value == '2') {
                    $language = "FRENCH";
                }
                if ($value == "1") {
                    $language = "ENGLISH";
                }
                $array["LANGUAGE"] = $language;
                $sxe->addChild('language', $language);
            }
            if ($key == "sampling_date") {//Traitement sampling date
                if ($value[0] == "") {
                } else {
                    if (count($value) > 1) {
                        if (count(array_unique($value)) < count($value)) {
                            $error = "Sampling date must be unique";
                            foreach ($value as $key => $value) {
                                if (\DateTime::createFromFormat('Y-m-d', $value) !== FALSE) {
                                    $array["SAMPLING_DATE"][$key] = htmlspecialchars($value, ENT_QUOTES);
                                } else {
                                    $error = "Sampling date invalid";
                                }
                            }
                        }
                        
                        else {
                            foreach ($value as $key => $value) {
                                if (\DateTime::createFromFormat('Y-m-d', $value) !== FALSE) {
                                    $array["SAMPLING_DATE"][$key] = htmlspecialchars($value, ENT_QUOTES);
                                } else {
                                    $error = "Sampling date invalid";
                                }
                            }
                        }
                    } else {
                        if (\DateTime::createFromFormat('Y-m-d', $value[0]) !== FALSE) {
                            $array["SAMPLING_DATE"][0] = htmlspecialchars($value[0], ENT_QUOTES);
                        } else {
                            $error = "Sampling date invalid";
                        }
                    }
                }
                
            }
            if ($key == "description") {//Traitement de la description
                $array["DATA_DESCRIPTION"] = htmlspecialchars($value, ENT_QUOTES);
                $description               = $descriptions->addChild('description', htmlspecialchars($value, ENT_QUOTES));
                $description->addAttribute('descriptionType', 'Abstract');
                
                
            }
            if ($key == "scientific_field") {//Traitement des scientific fields
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
            if ($key == "sampling_point_name") {//Traitement des samplingpoint
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
            
            if ($key == "measurement_nature") {//Traitement measurement
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
            
            if ($type == "Publish") {//Definition des droit d'acces si on publie
                if ($key == "access_right") {
                    if ($value == "Closed") {
                        $publication_date      = "9999-12-31";
                        $array["ACCESS_RIGHT"] = 'Closed';
                    } elseif ($value == "Open") {
                        $publication_date      = date('Y-m-d');
                        $array["ACCESS_RIGHT"] = 'Open';
                    } elseif ($value == "Embargoed") {
                        $today                 = date('Y-m-d');
                        $array["ACCESS_RIGHT"] = 'Embargoed';
                        $embargoeddate         = $_POST["publication_date"];
                        if ($today < $embargoeddate) {
                            $publication_date = htmlspecialchars($_POST["publication_date"], ENT_QUOTES);
                            ;
                        } else {
                            $error = "Invalid embargo date!";
                        }
                    } else {
                        $error = "Select a valid ACCESS RIGHT";
                    }
                    
                    
                    $array["PUBLICATION_DATE"] = $publication_date;
                }
            } elseif ($type == "Draft") { // Si c'est un brouillon on force le droit en Draft
                $array["ACCESS_RIGHT"]     = "Draft";
                $publication_date          = date('Y-m-d');
                $array["PUBLICATION_DATE"] = @$publication_date;
            }
            
            
            $array["METADATA_DATE"] = date("Y-m-d");
            
            
            if ($key == "file_already_uploaded") {//On check si l'admin ou si il s'agit d'un draft, les fichiers deja associé
                if (count($value) > 1) {
                    foreach ($value as $key => $value) {
                        $file_already_uploaded[$key]['DATA_URL'] = $value;
                    }
                } else {
                    $file_already_uploaded[0]['DATA_URL'] = $value[0];
                }
            }
            
        }
        
        
        if (!$error == NULL) {//si on rencontre une erreur on retourne le tableau et on l'affiche
            $array['dataform'] = $array;
            $array['error']    = $error;
            return $array;
        } else {//sinon on continue 
            if ($method == "Edit") { //Si on edit un fichier deja existant (Publié , ou draft)
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
            } else {//Nouveau fichier
                if ($type == "Draft") {
                    $newdoi            = uniqid('Draft-');
                    $array['dataform'] = $array;
                    $array['doi']      = $newdoi;
                    return $array;
                    
                } elseif ($type == "Publish") {
                    $newdoi = self::generateDOI();
                    if ($newdoi != false) {
                        $doi        = $config["DOI_PREFIX"] . "/" . $config['REPOSITORY_NAME']."-" . $newdoi;
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
    }
    
    
    function ManageDraft($db, $array)
    {
        if (isset($array['error'])) { //Si une erreur est detecté
            return $array;
        } else {
            $config        = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
            $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
            $doi           = $array['doi'];
            $collection    = "Manual_Depot";
            
            $query            = array(
                '_id' => $doi
            );
            $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
            $cursor           = $collectionObject->find($query);
            $tmparray         = array();
            if ($cursor->count() == 1) {//Verification si le draft existe deja
                foreach ($cursor as $key => $value) {
                    if ($value['INTRO']["UPLOAD_DATE"]) {
                        $array['dataform']['UPLOAD_DATE'] = $value['INTRO']['UPLOAD_DATE'];//Mise a jour de la date d'upload
                    }
                    if ($value['INTRO']["CREATION_DATE"]) {
                        $array['dataform']['CREATION_DATE'] = $value['INTRO']['CREATION_DATE'];//Mise a jour de la date de creation
                    }
                }
                foreach ($cursor as $key => $value) {//Recuperation des fichies de données dans la base
                    foreach ($value["DATA"]["FILES"] as $key => $value) {
                        $tmparray[] = $value;
                    }
                }
                
                $intersect = array();
                foreach ($tmparray as $key => $value) {//On parcourt les fichiers de la base
                    foreach ($array['file_already_uploaded'] as $key => $value2) {//On parcout ceux du formulaire pour voir les suppression eventuels
                        if ($value['DATA_URL'] == $value2['DATA_URL']) {
                            $intersect[] = $value;
                        }
                    }
                }
                
                
                for ($i = 0; $i < count($_FILES['file']['name']); $i++) {//On parcout les nouveau fichiers uploader
                    $repertoireDestination         = $UPLOAD_FOLDER;
                    $nomDestination                = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                    $data[$i]["DATA_URL"]          = $nomDestination;
                    
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
                
                if (count($intersect) != 0 and $data != 0) { //si il y a eu des suppressions et des ajouts
                    $merge = array_merge($intersect, $data); // on merge les tableaux 
                } else if (count($intersect) != 0) {// si il y a eu seulement des suppressions
                    $merge = $intersect;
                    
                } else {    //si il y a eu seuelement des ajouts
                    $merge = $data;
                    
                }
                
                $merge = array_map("unserialize", array_unique(array_map("serialize", $merge)));// on dedoublonne les tableaux
                mkdir($UPLOAD_FOLDER . "/" . $doi . "/tmp");//Creation d'un dossier temporaire de tri
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
                );//Json a envoyer a mongo
                $collectionObject->update(array(
                    '_id' => $doi
                ), $json);//Mise a jour de la base
                return $array['message'] = '   <div class="ui message grey"  style="display: block;">Draft edited! </div>';
            } else {// Si c'est un nouveau draft
                $array['dataform']["UPLOAD_DATE"]   = date('Y-m-d');
                $array['dataform']["CREATION_DATE"] = date('Y-m-d');
                if ($_FILES['file']['name'][0] != "") {//Check des fichier uploader 
                    for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                        $repertoireDestination                  = $UPLOAD_FOLDER;
                        $nomDestination                         = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                        $data['FILES'][$i]["DATA_URL"]          = $nomDestination;
                        
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
                                $data['FILES'][$i]["FILETYPE"] = $filetypes;
                                $collectionObject              = $this->db->selectCollection($config["authSource"], $collection);
                            } else {
                                $returnarray[] = "false";
                                $returnarray[] = $array['dataform'];
                                return $returnarray;
                            }
                        }
                        
                    }
                    
                } else {
                    $data["FILES"] = null;
                }
                
                $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
                
                $json = array(
                    '_id' => $doi,
                    "INTRO" => $array['dataform'],
                    "DATA" => $data
                );
                $collectionObject->insert($json);// on insert le nouveau Draft
                return $array['message'] = '   <div class="ui message grey"  style="display: block;">Draft created! </div>';
                
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
            if ($_FILES['file']['error'][0] == 4) { // on verifie qu'il y a au moins un fichier de donné lié
                $array['error'] = "Fichier manquant!";
                return $array;
            }
            for ($i = 0; $i < count($_FILES['file']['name']); $i++) { // on parcourt les fichiers uploader
                $repertoireDestination                  = $UPLOAD_FOLDER;
                $nomDestination                         = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                $data["FILES"][$i]["DATA_URL"]          = $nomDestination;
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
            $request = $Request->send_XML_to_datacite($array['xml']->asXML(), $doi); // on enovie les donné à datacite
            if ($request == "true") { // si datacite reponds et enregistre les données
                self::Increment_DOI($doi);
                $collectionObject->insert($json); // on insert dans la base
                $Request->Send_Mail_To_uploader($array['dataform']['FILE_CREATOR'], $array['dataform']['TITLE'], $doi, $array['dataform']['DATA_DESCRIPTION']); // Envoie d'un mail au auteurs du jeu de données
                return $array['message'] = '   <div class="ui message green"  style="display: block;">Dataset created!</div>';
            } else {
                self::UnlockDOI();
                $array['error'] = "Unable to send metadata to Datacite"; // Si datacite est indisponible on afficher une erreur
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
            if (strstr($doi, $config['REPOSITORY_NAME']) !== FALSE) { //Edition Si un DOI perrene est assigné
                
                if ($_SESSION['admin'] == 1) {//Si c'est un admin (On peut modiifer les fichiers)
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
                } else {//Si c'est l'utilisateur propriétaire on modifie juste les metadonnées
                    $json = array(
                        '$set' => array(
                            "INTRO" => $array['dataform']
                        )
                    );
                }
                $Request    = new RequestApi();
                $xml        = $array['xml'];
                $identifier = $xml->addChild('identifier', $doi);
                $identifier->addAttribute('identifierType', 'DOI');
                $request = $Request->send_XML_to_datacite($xml->asXML(), $doi);
                if ($request == "true") { //Si les donnnées ont bien été receptionné par datacite
                    $collectionObject->update(array(
                        '_id' => $doi
                    ), $json);
                    return $array['message'] = '   <div class="ui message green"  style="display: block;">Dataset edited!</div>';
                } else {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
            } elseif (strstr($doi, 'Draft') !== FALSE) { /// publication d'un draft
             $generatedoi=self::generateDOI();
                if ($generatedoi==false) {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
                $newdoi = $config['REPOSITORY_NAME']."-" . $generatedoi;//Generation d'un DOI
                $Request    = new RequestApi();
                $xml        = $array['xml'];
                $identifier = $xml->addChild('identifier', $config["DOI_PREFIX"] . "/" . $newdoi);
                $identifier->addAttribute('identifierType', 'DOI');
                $request = $Request->send_XML_to_datacite($xml->asXML(), $config["DOI_PREFIX"] . "/" . $newdoi);
                if ($request == "true") {
                    $collection       = "Manual_Depot";
                    $query            = array(
                        '_id' => $doi
                    );
                    $doi              = $array['doi'];
                    $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
                    $cursor           = $collectionObject->find($query);
                    for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                        $repertoireDestination         = $UPLOAD_FOLDER;
                        $nomDestination                = str_replace(' ', '_', $_FILES["file"]["name"][$i]);
                        $data[$i]["DATA_URL"]          = $nomDestination;
                        
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
                    foreach ($cursor as $key => $value) {
                        if ($value['INTRO']["UPLOAD_DATE"]) {
                            $array['dataform']['UPLOAD_DATE'] = $value['INTRO']['UPLOAD_DATE'];
                        }
                        if ($value['INTRO']["CREATION_DATE"]) {
                            $array['dataform']['CREATION_DATE'] = $value['INTRO']['CREATION_DATE'];
                        }
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
                    mkdir($UPLOAD_FOLDER . "/" . $config["DOI_PREFIX"] . "/" . $newdoi, 0777, true);
                    $query  = array(
                        '_id' => $doi
                    );
                    $cursor = $collectionObject->find($query);
                   
                    foreach ($merge as $key => $value) {
                        rename($UPLOAD_FOLDER . $doi . '/' . $value['DATA_URL'], $UPLOAD_FOLDER . "/" . $config["DOI_PREFIX"] . "/" . $newdoi . "/" . $value['DATA_URL']);
                    }
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
                        $DATA                                           = $value["DATA"];
                    }
                    $collectionObject->remove(array(
                        '_id' => $doi
                    ));
                    $newfiles['FILES'] = $merge;
                    
                    $collectionObject->insert(array(
                        '_id' => $config["DOI_PREFIX"] . "/" . $newdoi,
                        "INTRO" => $INTRO,
                        "DATA" => $newfiles
                    ));
                    self::Increment_DOI($doi);
                    $Request->Send_Mail_To_uploader($array['dataform']['FILE_CREATOR'], $array['dataform']['TITLE'], $config["DOI_PREFIX"] . "/" . $newdoi, $array['dataform']['DATA_DESCRIPTION']);
                    return $array['message'] = '   <div class="ui message green"  style="display: block;">Draft published!</div>';
                } else {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
                
            }
            
            else { //Publication d'un unpublished
                $generatedoi=self::generateDOI();
                if ($generatedoi==false) {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
                $newdoi = $config['REPOSITORY_NAME']."-" .$generatedoi;//Genreation d'un DOI

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
                    //unlink($ORIGINAL_DATA_URL);
                    exec("sudo -u ".$config["DATAFILE_UNIXUSER"]." rm ".$ORIGINAL_DATA_URL);
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
                    self::Increment_DOI($doi);
                    $Request->Send_Mail_To_uploader($array['dataform']['FILE_CREATOR'], $array['dataform']['TITLE'], $config["DOI_PREFIX"] . "/" . $newdoi, $array['dataform']['DATA_DESCRIPTION']);
                    return $array['message'] = '   <div class="ui message green"  style="display: block;">Dataset published!</div>';
                } else {
                    $array['error'] = "Unable to send metadata to Datacite";
                    return $array;
                }
                
            }
            
        }
    }
    
    
    /**
     * Remove datasheet or Draft
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
        $db               = self::connect_tomongo();
        $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
        $query            = array(
            '_id' => $doi
        );
        if (strstr($doi, $config['REPOSITORY_NAME']) !== FALSE) {//si jeu de données publié
            if ($_SESSION['admin'] == 1) {//check admin
                $cursor           = $collectionObject->find($query);
               foreach ($cursor as $key => $value) {
                foreach ($value["DATA"]["FILES"] as $key => $value) {
                    $data_url=$value["DATA_URL"];
                    unlink($UPLOAD_FOLDER . $doi . '/'. $data_url);//remove datafile
                }
            }
                $collectionObject->remove(array(
                    '_id' => $doi
                ));//Suppresion dans la base mongo
                rmdir($UPLOAD_FOLDER . $doi);//Suppresion du dossier
                $request = new RequestApi();
                $request->Inactivate_doi($doi);//Désactivation du DOi aupres de datacite
                
                return true;
            } 
            else { //sinon erreur
                    return false;
            }
        } else {// si draft ou unpublished
            $db               = self::connect_tomongo();
            $collectionObject = $this->db->selectCollection($config["authSource"], $collection);
            $query            = array(
                '_id' => $doi
            );
            $cursor           = $collectionObject->find($query);
            foreach ($cursor as $key => $value) {
                foreach ($value["DATA"]["FILES"] as $key => $value) {
                    $ORIGINAL_DATA_URL = $value["ORIGINAL_DATA_URL"];
                    $data_url=$value["DATA_URL"];
                    unlink($UPLOAD_FOLDER . $doi . '/'. $data_url);//remove datafile
                    if (strstr($doi, 'Draft') == FALSE) {  //Remove xls if unpublished from otelocloud
                        //unlink($ORIGINAL_DATA_URL);
                        exec("sudo -u ".$config["DATAFILE_UNIXUSER"]." rm ".$ORIGINAL_DATA_URL);

                     }
                }
            }
            $collectionObject->remove(array(
                '_id' => $doi
            ));//Suppresion de la base mongo
            rmdir($UPLOAD_FOLDER . $doi);//remove empty folder
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
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        if (!empty($object) && !empty($message) && filter_var($sendermail, FILTER_VALIDATE_EMAIL)) {
            $title = $response['_source']['INTRO']['TITLE'];
            $headers .= "From:<".$config['NO_REPLY_MAIL'].">\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
                if ($author_name == $value["NAME"] && $author_firstname == $value["FIRST_NAME"]) {
                    $mail = $value["MAIL"];
                    mail("<" . $mail . ">", 'Contact from '.$config['REPOSITORY_NAME'].': ' . $object, '<html> 
                            <head> 
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
                            </head> 
                            <body> 
                                <h2>Contact from :  <img src="'.$config['REPOSITORY_URL'].'/img/logo.png" alt="Logo " height="30" width="120" /> </h2>  
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