<?php
namespace search\controller;
use \search\controller\FileController as File;


Class MailerController
{


	function CheckSMTPstatus(){
		 	$file = new File();
	        $config=$file->ConfigFile();
	  		$f = fsockopen($config['SMTP'], 25,$errno,$errstr,3) ;
	  		$connected=false;
				if ($f !== false) {
				    $res = fread($f, 1024) ;
					if (strlen($res) > 0 && strpos($res, '220') === 0) {
					    $connected=true; 
					}
				}
			fclose($f) ;
			return $connected;
	}

    /**
     * Send a mail to author of a dataset
     * @param  doi of dataset , data of dataset,nom de l'auteur,prenom de l'auteur,object du mail,message, mail de l'expediteur
     * @return true if error else false
     */
    
    function Send_Mail_author($doi, $response, $author_name, $author_firstname, $object, $message, $sendermail)
    {
    	$connected=self::CheckSMTPstatus();
	if ($connected===true) {
            $file = new File();
    $config=$file->ConfigFile();

        if (!empty($object) && !empty($message) && filter_var($sendermail, FILTER_VALIDATE_EMAIL)) {
            $title = $response['_source']['INTRO']['TITLE'];
            $headers = "From:<".$config['NO_REPLY_MAIL'].">\r\n";
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
    else {
            return $error = "true";
        }
        
    }
        /**
     * Send a mail to contact ORDAR owner admin
     * @param object,message,mail of sender
     * @return true if error, else false
     */
    function Send_Contact_Mail($object, $message, $sendermail)
    {
  	$connected=self::CheckSMTPstatus();
	if ($connected===true) {
	

		        $file = new File();
		        $config=$file->ConfigFile();
		        if (!empty($object) && !empty($message) && filter_var($sendermail, FILTER_VALIDATE_EMAIL)) {
		            $headers = "From:<".$config['NO_REPLY_MAIL'].">\r\n";
		            $headers .= "MIME-Version: 1.0\r\n";
		            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
		            $mail = mail("<otelo-si@univ-lorraine.fr>", 'Contact from '.$config['REPOSITORY_NAME'].': ' . $object, '<html>
		    <head>
		    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		    </head>
		    <body>
		         <h2>Contact from :  <img src="'.$config['REPOSITORY_URL'].'/img/logo.png" alt="Logo" height="30" width="120" /> </h2>  
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
    }else {
            return $error = "true";
        }

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
      	$connected=self::CheckSMTPstatus();
	if ($connected===true) {
	        $file = new File();
	        $config=$file->ConfigFile();
	        $headers = "From:<".$config['NO_REPLY_MAIL'].">\r\n";
	        $headers .= "MIME-Version: 1.0\r\n";
	        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
	        foreach ($authors as $key => $value) {
	        $mail = mail($value['MAIL'], 'Dataset submit successfully! ', '<html>
	    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    </head>
	    <body>
	        <h2>Your dataset is now on <img src="'.$config['REPOSITORY_URL'].'/img/logo.png" alt="Logo " height="30" width="120" /></h2>
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
	  }  else{
  	$error="true";
  }
        return $error;
    }


    function DOIerror(){
    	$connected=self::CheckSMTPstatus();
	if ($connected===true) {
    	$file = new File();
        $config=$file->ConfigFile();
    	 foreach ($config["admin"] as $key => $value) {
                $array = explode(",", $value);
            }
            foreach ($array as $key => $value) {
                $headers = "From:<".$config['NO_REPLY_MAIL'].">\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                $mail = mail($value, 'Error in '.$config['REPOSITORY_NAME'], '<html>
                <body>
                    <h2>Error occured in '.$config['REPOSITORY_NAME'].'!</h2>
                    <p>This DOI '.$config['REPOSITORY_NAME'].'-' . $NewDOI . ' is already registered check your database DOI.<p>
                </body>
                </html> ', $headers);
            }
    	}
	}


   function Warning_mail($filename){
   	$connected=self::CheckSMTPstatus();
	if ($connected===true) {
        $file = new File();
        $config=$file->ConfigFile();
          foreach ($config["admin"] as $key => $value) {
                $array = explode(",", $value);
            }
            foreach ($array as $key => $value) {
        $headers = "From:<".$config['NO_REPLY_MAIL'].">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        $mail = mail($value, 'Error in '.$config['REPOSITORY_NAME'], '<html>
        <body>
            <h2>Error occured in '.$config['REPOSITORY_NAME'].'!</h2>
            <p>This file is published, but unpublished data '.$filename.' is not removed, please remove it and create an html file<p>
        </body>
        </html> ', $headers);
        	}
    	}
	}

     function Warning_mail_bad_path_data(){
     	$connected=self::CheckSMTPstatus();
	if ($connected===true) {
	        $file = new File();
	        $config=$file->ConfigFile();
	          foreach ($config["admin"] as $key => $value) {
	                $array = explode(",", $value);
	            }
	            foreach ($array as $key => $value) {
	        $headers = "From:<".$config['NO_REPLY_MAIL'].">\r\n";
	        $headers .= "MIME-Version: 1.0\r\n";
	        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
	        $mail = mail($value, 'Error in '.$config['REPOSITORY_NAME'], '<html>
	        <body>
	            <h2>Error occured in '.$config['REPOSITORY_NAME'].'!</h2>
	            <p>Your path for file is set to'.$config['UPLOAD_FOLDER'].', and it does not exist or have bad permissions. <p>
	        </body>
	        </html> ', $headers);
	        }
	    }
	}







}