<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \search\controller\RequestController as RequestApi;
use \search\controller\DatasheetController as Datasheet;
use \search\controller\FileController as File;



require '../vendor/autoload.php';


$c = new \Slim\Container();
$app = new \Slim\App($c);
session_start();


$app->get('/', function (Request $req,Response $responseSlim) {
$loader = new Twig_Loader_Filesystem('search/templates');
$twig = new Twig_Environment($loader);
if ($_SESSION) {
echo $twig->render('accueil.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail']]);
}
else{
	echo $twig->render('accueil.html.twig');
}
});

$app->get('/accueil', function (Request $req,Response $responseSlim) {
$loader = new Twig_Loader_Filesystem('search/templates');
$twig = new Twig_Environment($loader);
if ($_SESSION) {
echo $twig->render('accueil.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail']]);
}
else{
	echo $twig->render('accueil.html.twig');
}
});


$app->get('/about', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	echo $twig->render('about.html.twig');
});

$app->get('/terms', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	echo $twig->render('terms.html.twig');
});

$app->get('/contact', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	echo $twig->render('contact.html.twig');
});

$app->post('/contact', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	$sendermail = $req->getparam('User-email');
	$message = $req->getparam('User-message');
	$object = $req->getparam('User-object');
	$request= new RequestApi();
	$error=$request->Send_Contact_Mail($object,$message,$sendermail);
	echo $twig->render('contact_request.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],'error'=>$error]);});


$app->get('/searchresult', function (Request $req,Response $responseSlim) {
$loader = new Twig_Loader_Filesystem('search/templates');
$twig = new Twig_Environment($loader);
$query=$req->getparam('query');
if ($_SESSION) {
echo $twig->render('accueil.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],'query'=>$query]);
}
else{
	echo $twig->render('accueil.html.twig');
}
});

$app->get('/login', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	echo $twig->render('login.html.twig');
	//$_SESSION['name'] = $_SERVER['HTTP_SN'];
	//$_SESSION['firstname'] = $_SERVER['HTTP_GIVENNAME'];
	//$_SESSION['mail'] = $_SERVER['HTTP_MAIL'];
	$_SESSION['name'] = "guiot";
	$_SESSION['firstname'] ="anthony";
	$_SESSION['mail'] = "test@gf.gt";
	

	session_regenerate_id();

	//if ($_SERVER['HTTP_REFERER']) {
	//return $responseSlim->withRedirect($_SERVER['HTTP_REFERER']);
	//}	
	//else{
		return $responseSlim->withRedirect('accueil');
	//}

});

$app->get('/logout', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	session_destroy();
	return $responseSlim->withRedirect('accueil');

});

$app->get('/mypublications', function (Request $req,Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	if ($_SESSION) {
	echo $twig->render('mypublications.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail']]);
	}
	else{
		return $responseSlim->withRedirect('accueil');
	}

});

$app->get('/upload', function (Request $req,Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	if ($_SESSION) {
	echo $twig->render('upload.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail']]);
	}
	else{
		return $responseSlim->withRedirect('accueil');
	}
})->setName('upload');

$app->post('/upload', function (Request $req,Response $responseSlim) {
	 $loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
   	$Datasheet = new Datasheet();
   	$db=$Datasheet->connect_tomongo();
	$array=$Datasheet->Postprocessing($_POST);
	$response=$Datasheet->Newdatasheet($db,$array);
	
	if (array_key_exists('error', $response)) {
		echo $twig->render('upload.html.twig',['error'=>$response['error'],'name'=>$_SESSION['name'],'mail'=>$_SESSION['mail'],'firstname'=>$_SESSION['firstname'],'title' => $response['dataform']['TITLE'],'description'=>$response['dataform']['DATA_DESCRIPTION'],'creation_date'=>$response['dataform']['CREATION_DATE'],'sampling_dates'=>$response['dataform']['SAMPLING_DATE'],'authors'=>$response['dataform']['FILE_CREATOR'],'keywords'=>$response['dataform']['KEYWORDS'],'sample_kinds'=>$response['dataform']['SAMPLE_KIND'],'scientific_fields'=>$response['dataform']['SCIENTIFIC_FIELD'],'institutions'=>$response['dataform']['INSTITUTION'],'language'=>$response['dataform']['LANGUAGE'],'sampling_points'=>$response['dataform']['SAMPLING_POINT'],'measurements'=>$response['dataform']['MEASUREMENT'],'license'=>$license,'publisher'=>$response['dataform']['PUBLISHER'],'fundings'=>$response['dataform']['FUNDINGS'],'accessright'=>$response['dataform']['ACCESS_RIGHT'],'embargoed_date'=>$response['dataform']['PUBLICATION_DATE']]);
	}
	else{
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	echo $twig->render('uploadsuccess.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail']]);

	}
	//return $response;
	//return $responseSlim->withRedirect('upload');

});



$app->post('/getmypublications', function (Request $req,Response $responseSlim) {
	$request = new RequestApi();
	if ($_SESSION) {
		$authors_mail= $_SESSION['mail'];
		$authors_name= $_SESSION['name'];
		$query  = $req->getparam('query');

		if (!empty($query)) {
			$response=$request->getPublicationsofUser($authors_mail,$authors_name,$query);
		}
		else{
		$response=$request->getPublicationsofUser($authors_mail,$authors_name,"null");
		}
   	return $response;
	}
	else{
		return $responseSlim->withRedirect('accueil');
	}
});

$app->get('/record', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
   	$request= new RequestApi();
   	$id  = $req->getparam('id');
	$response=$request->get_info_for_dataset($id);
	if (isset($response['_source']['DATA'])){
		$files =  $response['_source']['DATA']['FILES'];
	}
	else{
		$files=NULL;
	}
	if ($response==false) {
				return $responseSlim->withRedirect('accueil');

	}
	else{
	return @$twig->render('viewdatadetails.html.twig', [
		'name'=>$_SESSION['name'],
		'firstname'=>$_SESSION['firstname'],
		'mail'=>$_SESSION['mail'],
        'doi'=> $response['_id'],
        'title' => $response['_source']['INTRO']['TITLE'],
        'datadescription'=>$response['_source']['INTRO']['DATA_DESCRIPTION'],
        'accessright'=>$response['_source']['INTRO']['ACCESS_RIGHT'],
        'publicationdate'=> $response['_source']['INTRO']['PUBLICATION_DATE'],
        'uploaddate'=>$response['_source']['INTRO']['UPLOAD_DATE'],
        'creationdate'=>$response['_source']['INTRO']['CREATION_DATE'],
        'authors'=>$response['_source']['INTRO']['FILE_CREATOR'],
        'files'=> $files,'mail'=>$_SESSION['mail'],
        'sampling_points'=> $response['_source']['INTRO']['SAMPLING_POINT'],
        'measurements'=> $response['_source']['INTRO']['MEASUREMENT'],
        'language'=> $response['_source']['INTRO']['LANGUAGE'],
        'institutions'=> $response['_source']['INTRO']['INSTITUTION'],
        'scientific_field'=> $response['_source']['INTRO']['SCIENTIFIC_FIELD'],
        'sampling_date'=> $response['_source']['INTRO']['SAMPLING_DATE'],
        'sample_kinds'=> $response['_source']['INTRO']['SAMPLE_KIND'],
        'keywords'=> $response['_source']['INTRO']['KEYWORDS'],
        'license'=> $response['_source']['INTRO']['LICENSE']
    	]);
	}
})->setName('record');


$app->get('/editrecord', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
   	$request= new RequestApi();
   	$id  = $req->getparam('id');
	$response=$request->get_info_for_dataset($id);
	if ($response==false) {
		return $responseSlim->withRedirect('accueil');
	}
	elseif($response['_source']['DATA']==null){
		return $responseSlim->withRedirect('accueil');
	}
	else{
		$found="false";
		foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
			if (@$_SESSION["mail"]==$value["MAIL"]) {
						$found="true";
			}
		}
		if ($found=="true") {
			
		
			$value=$response['_source']['INTRO']['LICENSE'];
			if ($value=="Creative commons Attribution alone") {
		 		$license=1;
		 	}
		 	elseif ($value=="Creative commons Attribution + ShareAlike") {
		 		$license=2;
		 	}
		 	elseif ($value=="Creative commons Attribution + Noncommercial") {
		 		$license=3;
		 	}
		 	elseif ($value=="Creative commons Attribution + NoDerivatives") {
		 		$license=4;
		 	}
		 	elseif ($value=="Creative commons Attribution + Noncommercial + ShareAlike") {
		 		$license=5;
		 	}
		 	elseif ($value=="Creative commons Attribution + Noncommercial + NoDerivatives") {
		 		$license=6;
		 	}

		return @$twig->render('edit_dataset.html.twig', ['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],
	        'doi'=>$id,'title' => $response['_source']['INTRO']['TITLE'],'description'=>$response['_source']['INTRO']['DATA_DESCRIPTION'],'creation_date'=>$response['_source']['INTRO']['CREATION_DATE'],'sampling_dates'=>$response['_source']['INTRO']['SAMPLING_DATE'],'authors'=>$response['_source']['INTRO']['FILE_CREATOR'],'keywords'=>$response['_source']['INTRO']['KEYWORDS'],'sample_kinds'=>$response['_source']['INTRO']['SAMPLE_KIND'],'scientific_fields'=>$response['_source']['INTRO']['SCIENTIFIC_FIELD'],'institutions'=>$response['_source']['INTRO']['INSTITUTION'],'language'=>$response['_source']['INTRO']['LANGUAGE'],'sampling_points'=>$response['_source']['INTRO']['SAMPLING_POINT'],'measurements'=>$response['_source']['INTRO']['MEASUREMENT'],'license'=>$license,'publisher'=>$response['_source']['INTRO']['PUBLISHER'],'fundings'=>$response['_source']['INTRO']['FUNDINGS'],'accessright'=>$response['_source']['INTRO']['ACCESS_RIGHT'],'embargoed_date'=>$response['_source']['INTRO']['PUBLICATION_DATE']
	    	]);
		}
	
	else{
		return $responseSlim->withRedirect('accueil');
	}
}
});

$app->post('/editrecord', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
   	$Datasheet = new Datasheet();
   	$doi = $req->getparam('id');
   	$request= new RequestApi();
	$response=$request->get_info_for_dataset($doi);
	$collection=$response['_type'];
	$doi= $response['_id'];
	$db=$Datasheet->connect_tomongo();
	$array=$Datasheet->Postprocessing($_POST);
	$return=$Datasheet->Editdatasheet($collection,$doi,$db,$array);
	if (array_key_exists('error', $return)) {
		$value=$response['_source']['INTRO']['LICENSE'];
			if ($value=="Creative commons Attribution alone") {
		 		$license=1;
		 	}
		 	elseif ($value=="Creative commons Attribution + ShareAlike") {
		 		$license=2;
		 	}
		 	elseif ($value=="Creative commons Attribution + Noncommercial") {
		 		$license=3;
		 	}
		 	elseif ($value=="Creative commons Attribution + NoDerivatives") {
		 		$license=4;
		 	}
		 	elseif ($value=="Creative commons Attribution + Noncommercial + ShareAlike") {
		 		$license=5;
		 	}
		 	elseif ($value=="Creative commons Attribution + Noncommercial + NoDerivatives") {
		 		$license=6;
		 	}
return @$twig->render('edit_dataset.html.twig', ['error'=>$return['error'],'name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],
	        'doi'=>$doi,'title' => $response['_source']['INTRO']['TITLE'],'description'=>$response['_source']['INTRO']['DATA_DESCRIPTION'],'creation_date'=>$response['_source']['INTRO']['CREATION_DATE'],'sampling_dates'=>$response['_source']['INTRO']['SAMPLING_DATE'],'authors'=>$response['_source']['INTRO']['FILE_CREATOR'],'keywords'=>$response['_source']['INTRO']['KEYWORDS'],'sample_kinds'=>$response['_source']['INTRO']['SAMPLE_KIND'],'scientific_fields'=>$response['_source']['INTRO']['SCIENTIFIC_FIELD'],'institutions'=>$response['_source']['INTRO']['INSTITUTION'],'language'=>$response['_source']['INTRO']['LANGUAGE'],'sampling_points'=>$response['_source']['INTRO']['SAMPLING_POINT'],'measurements'=>$response['_source']['INTRO']['MEASUREMENT'],'license'=>$license,'publisher'=>$response['_source']['INTRO']['PUBLISHER'],'fundings'=>$response['_source']['INTRO']['FUNDINGS'],'accessright'=>$response['_source']['INTRO']['ACCESS_RIGHT'],'embargoed_date'=>$response['_source']['INTRO']['PUBLICATION_DATE']
	    	]);	}
	else{

	return @$twig->render('editsuccess.html.twig');
	}
});




$app->post('/getinfo', function (Request $req,Response $responseSlim) {
    $request = new RequestApi();
	$query  = $req->getparam('query');
	$response=$request->requestToAPI($query);
   	return $response;
});

$app->get('/remove/{doi}', function (Request $req,Response $responseSlim,$args) {
	$Datasheet = new Datasheet();
	$request= new RequestApi();
   	$doi  = $args['doi'];
   	$response=$request->get_info_for_dataset($doi);
	$collection=$response['_type'];
	$doi= $response['_id'];
   	$Datasheet->removeUnpublishedDatasheet($collection,$doi);


});



$app->get('/files/{doi}/{filename}', function (Request $req,Response $responseSlim,$args) {
	$request= new RequestApi();
   	$doi  = $args['doi'];
   	$filename  = $args['filename'];
	$response=$request->get_info_for_dataset($doi);
	$File = new File();
	$download=$File->download($doi,$filename,$response);
	if ($download==NULL OR $download==false) {
		return $responseSlim->withStatus(403); 
	}

});

$app->get('/preview/{doi}/{filename}', function (Request $req,Response $responseSlim,$args) {
	$request= new RequestApi();
   	$doi  = $args['doi'];
   	$filename  = $args['filename'];
	$response=$request->get_info_for_dataset($doi);
	$File = new File();
	$download=$File->preview($doi,$filename,$response);
	if ($download==NULL OR $download==false) {
		return $responseSlim->withStatus(403); 
	}
	    	
	return @$responseSlim->withBody();		
});




$app->post('/contact_author', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	$Datasheet = new Datasheet();
	$request= new RequestApi();
	$author_name  = $req->getparam('author_name');
	$author_firstname  = $req->getparam('author_first_name');
	$author_name=htmlspecialchars($author_name, ENT_QUOTES);
	$author_firstname = htmlspecialchars($author_firstname, ENT_QUOTES);
	$doi = $req->getparam('doi');
	$doi = htmlspecialchars($doi, ENT_QUOTES);
	$sendermail = $req->getparam('User-email');
	$message = $req->getparam('User-message');
	$object = $req->getparam('User-object');
	$response=$request->get_info_for_dataset($doi);
	$error=$Datasheet->Send_Mail_author($doi,$response,$author_name,$author_firstname,$object,$message,$sendermail);
	echo $twig->render('contact_request.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],'error'=>$error]);

});



$app->run();