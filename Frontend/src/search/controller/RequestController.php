<?php
namespace search\controller;

use \search\controller\FileController as File;
use \search\controller\MailerController as Mailer;
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
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $rawData = curl_exec($ch);
        curl_close($ch);
        return $rawData;
    }

 public function get_ORCID_ID($code,$action)
    {
        $file   = new File();
        $config = $file->ConfigFile();
        $url = "https://orcid.org/oauth/token";
        $postcontent="client_id=".$config['ORCID_client_id']."&client_secret=".$config['ORCID_client_secret']."&grant_type=authorization_code&code=".$code."&redirect_uri=".$config['REPOSITORY_URL']."/".$action;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $postcontent,
        );
        $response                   = self::Curlrequest($url, $curlopt);
        return $response;
    }

    /**
     * Check status of datacite service
     * @return Code of request
     */
    public function Check_status_datacite()
    {
        $file   = new File();
        $config = $file->ConfigFile();

        //$handle = curl_init("https://mds.test.datacite.org");
        //$handle = curl_init("https://mds.datacite.org/metadata/10.24396/ORDAR-20");
        //curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        //$response = curl_exec($handle);
        //$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        //curl_close($handle);
 	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://mds.datacite.org/metadata/10.24396/ORDAR-20/');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	//curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //        "authorization: Basic SU5JU1QuT1RFTE86T3Tigqxsbw==",
        //        'Content-Type: text/xml',
        //    ));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml',
            ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode;
    }
    /**
     * Check if DOI already exist
     * @return false for not exist , true of exist and send mail to admin
     */
    public function Check_if_DOI_exist()
    {
        $file   = new File();
        $config = $file->ConfigFile();
        //$dbdoi  = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
        //    'authSource' => $config['DOI_database'],
        //    'username'   => $config['user_doi'],
        //    'password'   => $config['password_doi'],
        //));
        $dbdoi = new \MongoDB\Driver\Manager("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['DOI_database'],
            'username'   => $config['user_doi'],
            'password'   => $config['password_doi'],
        ));
        //$collection = $dbdoi->selectCollection($config['DOI_database'], "DOI");
        $query      = array(
            '_id' => $config['REPOSITORY_NAME'] . "-DOI",
        );
        //$cursor = $collection->find($query);
        $drvquery = new \MongoDB\Driver\Query($query, []);

	$cursor = $dbdoi->executeQuery($config['DOI_database'] . '.DOI', $drvquery);
	$tcursor = $cursor->toArray();
        $coll_count = count($tcursor);
        //if ($collection->count() != 1) {
        if ($coll_count != 1) {
            //$cursor = $collection->insert(array(
            //    '_id'   => $config['REPOSITORY_NAME'] . "-DOI",
            //    'ID'    => 0,
            //    'STATE' => "UNLOCKED",
            //));
	    $cursor = new \MongoDB\Driver\BulkWrite;
	    $cursor->insert(array(
                '_id'   => $config['REPOSITORY_NAME'] . "-DOI",
                'ID'    => 0,
                'STATE' => "UNLOCKED",
            ));
	    $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);

            $result       = $dbdoi->executeBulkWrite($config['DOI_database'], $cursor, $writeConcern);

        }
        //$collection = $dbdoi->selectCollection($config['DOI_database'], "DOI");
	$cursor = $dbdoi->executeQuery($config['DOI_database'] . '.DOI', $drvquery);

	$tcursor = $cursor->toArray();
        $coll_count = count($tcursor);
        //if ($collection->count() == 1) {
        if ($coll_count == 1) {
            //$query = array(
            //    'STATE' => 'UNLOCKED',
            //);
            //$cursor = $collection->find($query);
            //$count  = $cursor->count();
	    $query = new \MongoDB\Driver\Query(array('STATE' => 'UNLOCKED', []));
           $cursor = $dbdoi->executeQuery($config['DOI_database'] . '.DOI', $query);
	    $tcursor = $cursor->toArray();
           $count = count($tcursor);

            if ($count == 1) {
                //foreach ($cursor as $key => $value) {
                foreach ($tcursor as $key => $value) {
                    $DOI    = $value['ID'];
                    $NewDOI = ++$DOI;
                }
            }
        }

        $url     = "https://mds.test.datacite.org/metadata/" . $config['DOI_PREFIX'] . "/" . $config['REPOSITORY_NAME'] . "-" . $NewDOI;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml',
            ),
        );
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $DOI_exist = curl_exec($ch);
        $info      = curl_getinfo($ch);
        curl_close($ch);
        $url     = "https://mds.test.datacite.org/doi/" . $config['DOI_PREFIX'] . "/" . $config['REPOSITORY_NAME'] . "-" . $NewDOI;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml',
            ),
        );
        $ch2     = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch2, $curlopt);
        $URLisgenerated     = curl_exec($ch2);
        $URLisgeneratedinfo = curl_getinfo($ch2);
        curl_close($ch2);
        if ($info['http_code'] == 200 && $URLisgeneratedinfo['http_code'] == 200) {
            $Mail = new Mailer();
            $Mail->DOIerror();
            return true;
        } else {
            return false;
        }

    }
    /**
     * Desactivate DOI in Datacite
     * @return Code of request
     */
    public function Inactivate_doi($doi)
    {
        $file    = new File();
        $config  = $file->ConfigFile();
        $url     = "https://mds.test.datacite.org/metadata/" . $doi;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "DELETE",
            CURLOPT_HTTPHEADER     => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml',
            ),
        );
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $rawData = curl_exec($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);
    }

  
    public function requestDownloadnumber($query)
    {
        $file   = new File();
        $config = $file->ConfigFile();

        if (!empty($query)) { //Si des facets sont coché
            $query = rawurlencode($query); // on encode au format URL
        } else {
            $query = "*";
        }
        
        $bdd     = strtolower($config['authSource']);
        $url     = 'http://' . $config['ESHOST'] . '/logstash-*/_search?q="' . $query .'"';
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT           => $config['ESPORT'],
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_POSTFIELDS     => $postcontent,
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, true);
        foreach ($response["hits"]["hits"] as $key => $value) {
            $responses[$key]  = $value["_source"]["clientip"];
        }
        ;
        $array=array_unique($responses);
        $responses=count($array);
        return $responses;
    }



    /**
     * Make a request to elasticsearch API in admin MODE (View ALL)
     * @return data of request
     */
    public function requestToAPIAdmin($query, $from)
    {
        $file   = new File();
        $config = $file->ConfigFile();

        if (!empty($query)) { //Si des facets sont coché
            $query = rawurlencode($query); // on encode au format URL
        } else {
            $query = "*";
        }
        $postcontent = '{
            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} ,
             "_source": {
            "excludes": [ "DATA" ]
             },
            "aggs" : {
                "max_date" : { "max" : { "field" : "INTRO.CREATION_DATE" } },
                "min_date" : { "min" : { "field" : "INTRO.CREATION_DATE" } },
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
        $bdd     = strtolower($config['authSource']);

        $url     = 'http://' . $config['ESHOST'] . '/' . $bdd . '/_search?q=(' . $query . ')%20AND%20NOT%20INTRO.MEASUREMENT.ABBREVIATION:*_RAW%20AND%20NOT%20DATA.FILES.DATA_URL:*_RAW.XLSX%20AND%20NOT%20INTRO.ACCESS_RIGHT:Awaiting&size=10&from=' . $from;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT           => $config['ESPORT'],
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $postcontent,
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, true);
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
    public function requestToAPI($query, $from)
    {
        $file        = new File();
        $config      = $file->ConfigFile();
        $query       = str_replace(' ', '%20', $query);
        $query       = str_replace('+', '%20', $query);
        //$query=rawurlencode($query); 
        //var_dump($query);
        $postcontent = '{

            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} ,
            "_source": {
            "excludes": [ "DATA" ]
             },

            "aggs" : {
                 "max_date" : { "max" : { "field" : "INTRO.CREATION_DATE" } },
                "min_date" : { "min" : { "field" : "INTRO.CREATION_DATE" } },
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
        $bdd = strtolower($config['authSource']);
        $url = 'http://' . $config['ESHOST'] . '/' . $bdd . '/_search?q=(' . $query . ')%20AND%20NOT%20INTRO.ACCESS_RIGHT:Draft%20AND%20NOT%20INTRO.ACCESS_RIGHT:Awaiting&size=10&from=' . $from;

        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT           => $config['ESPORT'],
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $postcontent,
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, true);
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
    public function send_XML_to_datacite($XML, $doi)
    {
        $file    = new File();
        $config  = $file->ConfigFile();
        $url     = "https://mds.test.datacite.org/metadata/" . $doi;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_POSTFIELDS     => $XML,
            CURLOPT_HTTPHEADER     => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml',
            ),
        );
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $XMLondatacite = curl_exec($ch);
        curl_close($ch);

        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $DOI_exist = curl_exec($ch);
        $info      = curl_getinfo($ch);
        curl_close($ch);
        $url     = "https://mds.test.datacite.org/doi/" . $doi;
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => array(
                "authorization: " . $config['Auth_config_datacite'],
                'Content-Type: text/xml',
            ),
        );
        $ch2     = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url,
        ) + $curlopt;
        curl_setopt_array($ch2, $curlopt);
        $URLisgenerated     = curl_exec($ch2);
        $URLisgeneratedinfo = curl_getinfo($ch2);
        curl_close($ch2);
        if ($XMLondatacite == $XML && $URLisgeneratedinfo['http_code'] == 200) {
            return "true";
        } else {
            $url     = "https://mds.test.datacite.org/metadata/";
            $curlopt = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $XML,
                CURLOPT_HTTPHEADER     => array(
                    "authorization: " . $config['Auth_config_datacite'],
                    'Content-Type: text/xml',
                ),
            );
            $ch      = curl_init();
            $curlopt = array(
                CURLOPT_URL => $url,
            ) + $curlopt;
            curl_setopt_array($ch, $curlopt);
            $rawData = curl_exec($ch);
            $info    = curl_getinfo($ch);
            curl_close($ch);
            if ($info['http_code'] == "201") {
                $url_doi = urlencode($config['REPOSITORY_URL'] . "/record?id=" . $doi);
                $curl    = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL            => "https://mds.test.datacite.org/doi",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => "",
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => "POST",
                    CURLOPT_POSTFIELDS     => "doi=" . $doi . "&url=" . $url_doi,
                    CURLOPT_HTTPHEADER     => array(
                        "authorization: " . $config['Auth_config_datacite'],
                        "cache-control: no-cache",
                    ),
                ));
                $response = curl_exec($curl);
                $info     = curl_getinfo($curl);
                curl_close($curl);
                if ($info['http_code'] == "201") {
                    return "true";
                } else {
                    return "false";
                }
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
    public function getPublicationsofUser($author_mail, $authors_name, $query, $from)
    {
        $file        = new File();
        $config      = $file->ConfigFile();
        $postcontent = '{
            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} ,
            "_source": {
            "excludes": [ "DATA" ]
             },
            "aggs" : {

                "max_date" : { "max" : { "field" : "INTRO.CREATION_DATE" } },
                "min_date" : { "min" : { "field" : "INTRO.CREATION_DATE" } },
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
        $bdd = strtolower($config['authSource']);
        if ($query == "null") {
            // SI pas de facets
            $url = 'http://' . $config['ESHOST'] . '/' . $bdd . '/_search?q=INTRO.FILE_CREATOR.MAIL:' . $author_mail . '%20AND%20NOT%20INTRO.MEASUREMENT.ABBREVIATION:*_RAW%20AND%20NOT%20DATA.FILES.DATA_URL:*_RAW.XLSX%20AND%20NOT%20INTRO.ACCESS_RIGHT:Awaiting&size=10&from=' . $from;
        } else {
            // Sinon on recher avec les facets
            $query = rawurlencode($query);
            $url   = 'http://' . $config['ESHOST'] . '/' . $bdd . '/_search?q=' . $query . '%20AND%20(INTRO.FILE_CREATOR.MAIL:' . $author_mail . ')%20AND%20NOT%20INTRO.MEASUREMENT.ABBREVIATION:*_RAW%20AND%20NOT%20DATA.FILES.DATA_URL:*_RAW.XLSX%20AND%20NOT%20INTRO.ACCESS_RIGHT:Awaiting&size=10&from=' . $from;
        }
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT           => $config['ESPORT'],
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $postcontent,
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, true);
        $responses["hits"]["total"] = $response["hits"]["total"];
        $responses['aggregations']  = $response['aggregations'];
        foreach ($response["hits"]["hits"] as $key => $value) {
//Reformatage de la reponse
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
    public function get_info_for_dataset($id)
    {
        $file    = new File();
        $config  = $file->ConfigFile();
        $bdd     = strtolower($config['authSource']);
        $url     = 'http://' . $config['ESHOST'] . '/' . $bdd . '/_all/' . urlencode($id);
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT           => $config['ESPORT'],
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 40,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
        );
        $response = self::Curlrequest($url, $curlopt);
        $response = json_decode($response, true);
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
                foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                    if (@$_SESSION["mail"] == $value["MAIL"]) {
// on cherche si le mail de l'utilisateur courant est autorisé
                        return $response;
                    } else {
                        $notfound = "notfound";
                    }
                }
                if ($notfound = "notfound") {
//Si non autorisé on ne lui donne pas accés aux fichiers
                    unset($response['_source']['DATA']);
                    return $response;
                }

            } elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Closed") {
                foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                    if (@$_SESSION["mail"] == $value["MAIL"]) {
// on cherche si le mail de l'utilisateur courant est autorisé
                        return $response;
                    } else {
                        $notfound = "notfound";
                    }
                }
                if ($notfound = "notfound") {
//Si non autorisé on ne lui donne pas accés aux fichiers
                    unset($response['_source']['DATA']);
                    return $response;
                }
            } elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Unpublished") {
                $found = "false";
                foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                    if (@$_SESSION["mail"] == $value["MAIL"]) {
// on cherche si le mail de l'utilisateur courant est autorisé
                        $found = "true";
                    }
                }
                if ($found == "true") {
                    return $response;
                } else {
                    unset($response['_source']['DATA']);
                    return $response;
                }
            } elseif ($response["_source"]["INTRO"]["ACCESS_RIGHT"] == "Draft") {
                $found = "false";
                foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                    if (@$_SESSION["mail"] == $value["MAIL"]) {
// on cherche si le mail de l'utilisateur courant est autorisé
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
