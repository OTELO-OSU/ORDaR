<?php
namespace search\controller;


Class FileController
{
    
    /**
     * Download a file
     * @param doi of dataset, filename,data of dataset
     * @return true if ok else false
     */
    function download($doi, $filename, $response)
    {
        $config        = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
        $DOI_PREFIX    = $config["DOI_PREFIX"];
        $doi           = str_replace($config["UPLOAD_FOLDER"], "", $doi);
        if (isset($response['_source']['DATA'])) {
            if (strstr($doi, $config['REPOSITORY_NAME']) !== FALSE) {
                $file = $UPLOAD_FOLDER . $DOI_PREFIX . "/" . $doi . "/" . $filename;
            } else {
                $file = $UPLOAD_FOLDER . $doi . "/" . $filename;
            }
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Disposition: attachment; filename=" . $filename);
            $readfile = file_get_contents($file);
            print $readfile;
            if ($readfile == false) {
                return false;
            } else {
                return true;
            }
            exit;
        }
        
    }
    
    /**
     * Export to datacite xml format
     * @param data of dataset
     * @return true if ok else false
     */
    function export_to_datacite_xml($response)
    {
        if (isset($response['_source']['INTRO'])) {
            $sxe = new \SimpleXMLElement("<resource/>");
            $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $sxe->addAttribute('xmlns', 'http://datacite.org/schema/kernel-4');
            $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4/metadata.xsd');
            
            $identifier = $sxe->addChild('identifier', $response['_id']);
            $identifier->addAttribute('identifierType', 'DOI');
            $creators = $sxe->addChild('creators');
            foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
                $creator = $creators->addChild('creator');
                $creator->addChild('creatorName', $value['DISPLAY_NAME']);
            }
            $titles = $sxe->addChild('titles');
            
            $title           = $titles->addChild('title', $response['_source']['INTRO']['TITLE']);
            $publisher       = $sxe->addChild('publisher', $response['_source']['INTRO']['PUBLISHER']);
            $publicationYear = $sxe->addChild('publicationYear', $response['_source']['INTRO']['PUBLICATION_DATE']);
            $subjects        = $sxe->addChild('subjects');
            foreach ($response['_source']['INTRO']['SCIENTIFIC_FIELD'] as $key => $value) {
                $subjects->addChild('subject', $value['NAME']);
            }
            
            $RessourceType = $sxe->addChild('resourceType', 'Dataset');
            $sxe->addChild('language', $response['_source']['INTRO']['LANGUAGE']);
            $RessourceType->addAttribute('resourceTypeGeneral', 'Dataset');
            $Version      = $sxe->addChild('version', '1');
            $descriptions = $sxe->addChild('descriptions');
            $description  = $descriptions->addChild('description', $response['_source']['INTRO']['DATA_DESCRIPTION']);
            $description->addAttribute('descriptionType', 'Abstract');
            
            return $sxe;
        } else {
            return false;
        }
        
    }
    
    /**
     * Export to dublincore xml format
     * @param data of dataset
     * @return true if ok else false
     */
    function export_to_dublincore_xml($response)
    {
        if (isset($response['_source']['INTRO'])) {
            $sxe = new \SimpleXMLElement("<oai_dc:dc/>");
            $sxe->addAttribute('xmlns:xmlns:dc', 'http://purl.org/dc/elements/1.1/');
            $sxe->addAttribute('xmlns:xmlns:oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
            $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
            
            $identifier = $sxe->addChild('dc:dc:identifier', $response['_id']);
            foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
                $sxe->addChild('dc:dc:creator', $value['DISPLAY_NAME']);
            }
            
            $sxe->addChild('dc:dc:title', $response['_source']['INTRO']['TITLE']);
            $publisher       = $sxe->addChild('dc:dc:publisher', $response['_source']['INTRO']['PUBLISHER']);
            $publicationYear = $sxe->addChild('dc:dc:publicationYear', $response['_source']['INTRO']['PUBLICATION_DATE']);
            foreach ($response['_source']['INTRO']['SCIENTIFIC_FIELD'] as $key => $value) {
                $sxe->addChild('dc:dc:subject', $value['NAME']);
            }
            
            $RessourceType = $sxe->addChild('dc:dc:resourceType', 'Dataset');
            $sxe->addChild('language', $response['_source']['INTRO']['LANGUAGE']);
            $RessourceType->addAttribute('dc:dc:resourceTypeGeneral', 'Dataset');
            $sxe->addChild('dc:dc:description', $response['_source']['INTRO']['DATA_DESCRIPTION']);
            
            return $sxe;
        } else {
            return false;
        }
        
    }
    
    /**
     * Export to bibtex format
     * @param data of dataset
     * @return true if ok else false
     */
    function export_to_Bibtex($response)
    {
        if (isset($response['_source']['INTRO'])) {
            foreach ($response['_source']['INTRO']['FILE_CREATOR'] as $key => $value) {
                $authors .= $value['DISPLAY_NAME'] . ",";
            }
            $title       = $response['_source']['INTRO']['TITLE'];
            $description = $response['_source']['INTRO']['DATA_DESCRIPTION'];
            $year        = $response['_source']['INTRO']['PUBLICATION_DATE'];
            $doi         = $response['_id'];
            $bibtex      = " 
             @data{ 
              author       = {" . $authors . "}, 
              title        = {{" . $title . "}}, 
              description  = {{" . $description . "}}, 
              year         = " . $year . ", 
              doi          = {" . $doi . "}, 

            }";
            return $bibtex;
        } else {
            return false;
        }
    }
    
    /**
     * Preview a file
     * @param doi of dataset, filename,data of dataset
     * @return true if ok else false
     */
    function preview($doi, $filename, $response)
    {
        $config        = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $UPLOAD_FOLDER = $config["UPLOAD_FOLDER"];
        $DOI_PREFIX    = $config["DOI_PREFIX"];
        
        $doi = str_replace($config["UPLOAD_FOLDER"], "", $doi);
        if (isset($response['_source']['DATA'])) {
            if (strstr($doi, $config['REPOSITORY_NAME']) !== FALSE) {
                $file = $UPLOAD_FOLDER . $DOI_PREFIX . "/" . $doi . "/" . $filename;
            } else {
                $file = $UPLOAD_FOLDER . $doi . "/" . $filename;
            }
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Disposition: inline; filename='" . $filename."'");
            foreach ($response['_source']['DATA']['FILES'] as $key => $value) {
                if ($filename == $value["DATA_URL"]) {
                    $mime = $value["FILETYPE"];
                }
                
            }
            
            if ($mime == "pdf") {
                $readfile = readfile($file);
                $mime     = "application/pdf";
                header('Content-Type:  ' . $mime);

            } elseif ($mime == 'csv') {
                $file            = fopen($file, "r");
                $firstTimeHeader = true;
                $firstTimeBody   = true;
                echo '<link rel="stylesheet" type="text/css" href="/css/semantic/dist/semantic.min.css">';
                echo '    <link rel="stylesheet" type="text/css" href="/css/style.css">  
';
                echo "<div class='' ui grid container'  style='overflow-x:auto'><table style='width:700px; height:500px;' class='ui compact unstackable table'></div>";
                while (!feof($file)) {
                    $data = fgetcsv($file);
                    
                    if ($firstTimeHeader) {
                        echo "<thead>";
                    } else {
                        if ($firstTimeBody) {
                            echo "</thead>";
                            echo "<tbody>";
                            $firstTimeBody = false;
                        }
                    }
                    echo "<tr>";
                    
                    foreach ($data as $value) {
                        if ($firstTimeHeader) {
                            echo "<th>" . $value . "</th>";
                        } else {
                            echo "<td>" . $value . "</td>";
                        }
                    }
                    
                    echo "</tr>";
                    if ($firstTimeHeader) {
                        $firstTimeHeader = false;
                    }
                }
                echo "</table>";
            } elseif ($mime == 'txt' OR $mime == 'sh' OR $mime == 'py') {
                $readfile = readfile($file);
                $mime     = "text/plain";
                header('Content-Type:  ' . $mime);
            } elseif ($mime == 'png') {
                $readfile = readfile($file);
                $mime     = "image/png";
                header('Content-Type:  ' . $mime);
            } elseif ($mime == 'jpg') {
                $readfile = readfile($file);
                $mime     = "image/jpg";
                header('Content-Type:  ' . $mime);
            } elseif ($mime == 'gif') {
                $readfile = readfile($file);
                $mime     = "image/gif";
                header('Content-Type:  ' . $mime);
            }
            
            else {
                echo "<h1>Cannot preview file</h1> <p>Sorry, we are unfortunately not able to preview this file.<p>";
                $readfile = false;
                header('Content-Type:  text/html');
            }
            
            if ($readfile == false) {
                return false;
            } else {
                return $mime;
            }
            exit;
        }
        
    }
    
}