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
if ($_SESSION) {
echo $twig->render('accueil.html.twig',['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail']]);
}
else{
	echo $twig->render('accueil.html.twig');
}
});

$app->get('/login', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	echo $twig->render('login.html.twig');
	$_SESSION['name'] = 'Hissler';
	$_SESSION['firstname'] = 'a';
	$_SESSION['mail'] = 'christophe.hissler@list.lu';
	if ($_SERVER['HTTP_REFERER']) {
	return $responseSlim->withRedirect($_SERVER['HTTP_REFERER']);
	}	
	else{
		return $responseSlim->withRedirect('accueil');
	}

});

$app->get('/logout', function (Request $req,Response $responseSlim) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
	//echo $twig->render('login.html.twig');
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
		echo "login first";
	}
})->setName('upload');

$app->post('/upload', function (Request $req,Response $responseSlim) {
	 $loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
   	$Datasheet = new Datasheet();
   	$db=$Datasheet->connect_tomongo();
	$array=$Datasheet->Postprocessing($_POST);
	$response=$Datasheet->Newdatasheet($db,$array);
	if ($response==false) {
		echo $twig->render('upload.html.twig',['error'=>"true",'name'=>$_SESSION['name'],'mail'=>$_SESSION['mail'],'firstname'=>$_SESSION['firstname'],'creation_date'=>$_POST['creation_date'],'language'=> $_POST['language'],'sample_kind'=> $_POST['sample_kind'],'title'=> $_POST['title'],'description'=> $_POST['description'],'scientific_field' => $_POST['scientific_field']]);
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
		$authors= $_SESSION['mail'];
		$query  = $req->getparam('query');

		if (!empty($query)) {
			$response=$request->getPublicationsofUser($authors,$query);
		}
		else{
		$response=$request->getPublicationsofUser($authors,"null");
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
	return @$twig->render('viewdatadetails.html.twig', ['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],
        'doi'=> $response['_id'],'title' => $response['_source']['INTRO']['TITLE'],'datadescription'=>$response['_source']['INTRO']['DATA_DESCRIPTION'],'accessright'=>$response['_source']['INTRO']['ACCESS_RIGHT'],'publicationdate'=> $response['_source']['INTRO']['PUBLICATION_DATE'],'uploaddate'=>$response['_source']['INTRO']['UPLOAD_DATE'],'creationdate'=>$response['_source']['INTRO']['CREATION_DATE'],'authors'=>$response['_source']['INTRO']['FILE_CREATOR'],'files'=> $files,'mail'=>$_SESSION['mail'],'stations'=> $response['_source']['INTRO']['STATION'],'measurements'=> $response['_source']['INTRO']['MEASUREMENT']
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
	else{
		$value=$response['_source']['INTRO']['LICENSE'];
		if ($value=="Attribution alone (CC BY)") {
	 		$license=1;
	 	}
	 	elseif ($value=="Attribution + ShareAlike (CC BY-SA)") {
	 		$license=2;
	 	}
	 	elseif ($value=="Attribution + Noncommercial (CC BY-NC)") {
	 		$license=3;
	 	}
	 	elseif ($value=="Attribution + NoDerivatives (CC BY-ND)") {
	 		$license=4;
	 	}
	 	elseif ($value=="Attribution + Noncommercial + ShareAlike (CC BY-NC-SA)") {
	 		$license=5;
	 	}
	 	elseif ($value=="Attribution + Noncommercial + NoDerivatives (CC BY-NC-ND)") {
	 		$license=6;
	 	}

	return @$twig->render('edit_dataset.html.twig', ['name'=>$_SESSION['name'],'firstname'=>$_SESSION['firstname'],'mail'=>$_SESSION['mail'],
        'doi'=>$id,'title' => $response['_source']['INTRO']['TITLE'],'description'=>$response['_source']['INTRO']['DATA_DESCRIPTION'],'creation_date'=>$response['_source']['INTRO']['CREATION_DATE'],'sampling_date'=>$response['_source']['INTRO']['SAMPLING_DATE'][0],'authors'=>$response['_source']['INTRO']['FILE_CREATOR'],'keywords'=>$response['_source']['INTRO']['KEYWORDS'],'sample_kind'=>$response['_source']['INTRO']['SAMPLE_KIND'][0]['NAME'],'scientific_fields'=>$response['_source']['INTRO']['SCIENTIFIC_FIELD'],'institutions'=>$response['_source']['INTRO']['INSTITUTION'],'language'=>$response['_source']['INTRO']['LANGUAGE'],'stations'=>$response['_source']['INTRO']['STATION'],'measurements'=>$response['_source']['INTRO']['MEASUREMENT'],'license'=>$license,'publisher'=>$response['_source']['INTRO']['PUBLISHER'],'fundings'=>$response['_source']['INTRO']['FUNDINGS'],'accessright'=>$response['_source']['INTRO']['ACCESS_RIGHT'],'embargoed_date'=>$response['_source']['INTRO']['PUBLICATION_DATE']
    	]);
	}
});

$app->post('/editrecord/{doi}', function (Request $req,Response $responseSlim,$args) {
	$loader = new Twig_Loader_Filesystem('search/templates');
	$twig = new Twig_Environment($loader);
   	$Datasheet = new Datasheet();
   	$doi  = $args['doi'];
   	$request= new RequestApi();
	$response=$request->get_info_for_dataset($doi);
	$collection=$response['_type'];
	$doi= $response['_id'];
	$db=$Datasheet->connect_tomongo();
	$array=$Datasheet->Postprocessing($_POST);
	$response=$Datasheet->Editdatasheet($collection,$doi,$db,$array);
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