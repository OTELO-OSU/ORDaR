<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \search\controller\RequestController as RequestApi;
use \search\controller\DatasheetController as Datasheet;
use \search\controller\FileController as File;

require '../vendor/autoload.php';

$c = new \Slim\Container();
$app = new \Slim\App($c);
$container = $app->getContainer();
$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig');
    };
};

$c['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('forbidden.html.twig');
    };
};

session_start();

//Route permettant d'acceder a l'accueil
$app->get('/', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    if ($_SESSION['name']) {
        echo $twig->render('accueil.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);
    }
    else {
        echo $twig->render('accueil.html.twig');
    }
});
//Route permettant d'acceder à l'accueil
$app->get('/accueil', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    if ($_SESSION['name']) {
        echo $twig->render('accueil.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);
    }
    else {
        echo $twig->render('accueil.html.twig');
    }
});

//Route permettant d'acceder a la page about
$app->get('/about', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    echo $twig->render('about.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
});
//Route permettant d'acceder a la page terms of use
$app->get('/terms', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    echo $twig->render('terms.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
});

//Route receptionnant les données POST contact
$app->post('/contact', function (Request $req, Response $responseSlim) {
	if ($_SERVER['HTTP_REFERER'] != NULL){
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    $sendermail = $req->getparam('User-email');
    $message = $req->getparam('User-message');
    $object = $req->getparam('User-object');
    $request = new RequestApi();
    $error = $request->Send_Contact_Mail($object, $message, $sendermail);
    echo $twig->render('contact_request.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'error' => $error]);	
	}
	else{
		        return $responseSlim->withStatus(403);
	}
});

//Route affichant les resultats
$app->get('/searchresult', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    $query = $req->getparam('query');
    if ($_SESSION['name']) {
        echo $twig->render('accueil.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'query' => $query]);
    }
    else {
        echo $twig->render('accueil.html.twig', ['query' => $query]);
    }
});

//Route permettant la connexion d'un utilisateur
$app->get('/login', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    echo $twig->render('login.html.twig');
    $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
   // $_SESSION['name'] = $_SERVER['HTTP_SN'];
   // $_SESSION['firstname'] = $_SERVER['HTTP_GIVENNAME'];
   // $_SESSION['mail'] = $_SERVER['HTTP_MAIL'];
    $_SESSION['name'] = "t";
    $_SESSION['firstname'] = "t";
    $_SESSION['mail'] = "emmanuell.montarges@univ-lorraine.fr";
   
   
    foreach ($config["admin"] as $key => $value) {
        $array = explode(",", $value);
    }
    foreach ($array as $key => $value) {
        if ($value == $_SESSION['mail']) {
            $_SESSION['admin'] = "1";
        }
    }

    session_regenerate_id();

    if ($_SESSION['HTTP_REFERER']) {
        return $responseSlim->withRedirect($_SESSION['HTTP_REFERER']);
    }
    else {
        return $responseSlim->withRedirect('accueil');
    }

});

//Route permettant la deconnexion d'un utilisateur
$app->get('/logout', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    session_destroy();
    $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
    return $responseSlim->withRedirect($config['URL_DOI'].'/Shibboleth.sso/Logout?return='.$config['URL_DOI']);

});

//Route affichant les publication de l'utilisateur connecté
$app->get('/mypublications', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    if ($_SESSION['name']) {
        echo $twig->render('mypublications.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);
    }
    else {
        return $responseSlim->withRedirect('accueil');
    }

});

//Route affichant le formulaire d'upload
$app->get('/upload', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    if ($_SESSION['name']) {
        $nameKey = $this
            ->csrf
            ->getTokenNameKey();
        $valueKey = $this
            ->csrf
            ->getTokenValueKey();
        $name = $req->getAttribute($nameKey);
        $value = $req->getAttribute($valueKey);
        $request = new RequestApi();
        $dataset = new Datasheet();
        $status = $request->Check_status_datacite();
        $doi_already_exist = $request->Check_if_DOI_exist();
        if ($status == 200 && $doi_already_exist==false) {
            echo $twig->render('upload.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'name_CSRF' => $name, 'value_CSRF' => $value]);
        }
        else {
            echo $twig->render('error_datacite.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
        }

    }
    else {
        return $responseSlim->withRedirect('accueil');
    }
})
    ->setName('upload')
    ->add($container->get('csrf'));

//Route receptionnant les données POST de l'upload
$app->post('/upload', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf = $req->getAttribute($nameKey);
    $valuecsrf = $req->getAttribute($valueKey);
    $Datasheet = new Datasheet();
    $db = $Datasheet->connect_tomongo();
    $response = $Datasheet->Postprocessing($_POST, "Upload", "0",$db,'Manual_Depot');
    
    if (isset($response['error'])) {
        $value = $response['dataform']['LICENSE'];
        if ($value == "Creative commons Attribution alone") {
            $license = 1;
        }
        elseif ($value == "Creative commons Attribution + ShareAlike") {
            $license = 2;
        }
        elseif ($value == "Creative commons Attribution + Noncommercial") {
            $license = 3;
        }
        elseif ($value == "Creative commons Attribution + NoDerivatives") {
            $license = 4;
        }
        elseif ($value == "Creative commons Attribution + Noncommercial + ShareAlike") {
            $license = 5;
        }
        elseif ($value == "Creative commons Attribution + Noncommercial + NoDerivatives") {
            $license = 6;
        }
        echo $twig->render('upload.html.twig', ['error' => $response['error'], 'name' => $_SESSION['name'], 'mail' => $_SESSION['mail'], 'firstname' => $_SESSION['firstname'], 'title' => $response['dataform']['TITLE'], 'description' => $response['dataform']['DATA_DESCRIPTION'], 'creation_date' => $response['dataform']['CREATION_DATE'], 'sampling_dates' => $response['dataform']['SAMPLING_DATE'], 'authors' => $response['dataform']['FILE_CREATOR'], 'keywords' => $response['dataform']['KEYWORDS'], 'sample_kinds' => $response['dataform']['SAMPLE_KIND'], 'scientific_fields' => $response['dataform']['SCIENTIFIC_FIELD'], 'institutions' => $response['dataform']['INSTITUTION'], 'language' => $response['dataform']['LANGUAGE'], 'sampling_points' => $response['dataform']['SAMPLING_POINT'], 'measurements' => $response['dataform']['MEASUREMENT'], 'license' => $license, 'publisher' => $response['dataform']['PUBLISHER'], 'fundings' => $response['dataform']['FUNDINGS'], 'accessright' => $response['dataform']['ACCESS_RIGHT'], 'embargoed_date' => $response['dataform']['PUBLICATION_DATE'], 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf]);
    }
    else {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('uploadsuccess.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);

    }
    //return $response;
    //return $responseSlim->withRedirect('upload');
    
})->add($container->get('csrf'));

//Route receptionnant les données POST mypublications
$app->post('/getmypublications', function (Request $req, Response $responseSlim) {
    $request = new RequestApi();
    if ($_SESSION['admin'] == "1") {
        $query = $req->getparam('query');
        $response = $request->requestToAPIAdmin($query);
        return $response;
    }
    else {
        if ($_SESSION['name']) {
            $authors_mail = $_SESSION['mail'];
            $authors_name = $_SESSION['name'];
            $query = $req->getparam('query');

            if (!empty($query)) {
                $response = $request->getPublicationsofUser($authors_mail, $authors_name, $query);
            }
            else {
                $response = $request->getPublicationsofUser($authors_mail, $authors_name, "null");
            }
            return $response;
        }
        else {
            return $responseSlim->withRedirect('accueil');
        }
    }
});

//Route affichant les details d'un dataset
$app->get('/record', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    $request = new RequestApi();
    $id = $req->getparam('id');
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $name = $req->getAttribute($nameKey);
    $value = $req->getAttribute($valueKey);
    $response = $request->get_info_for_dataset($id, "Restricted");
    if (isset($response['_source']['DATA'])) {
        $files = $response['_source']['DATA']['FILES'];
    }
    else {
        $files = NULL;
    }
    if ($response == false) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;

    }
    else {

        if (strstr($id, 'ORDAR') !== false) {
            $id = split("/", $response['_id']);
            $id = $id[1];
        }
        else {
            $id = $id;
        }

        return @$twig->render('viewdatadetails.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'doi' => $response['_id'], 'admin' => $_SESSION['admin'], 'id' => $id, 'title' => $response['_source']['INTRO']['TITLE'], 'datadescription' => nl2br($response['_source']['INTRO']['DATA_DESCRIPTION']), 'accessright' => $response['_source']['INTRO']['ACCESS_RIGHT'], 'publicationdate' => $response['_source']['INTRO']['PUBLICATION_DATE'], 'uploaddate' => $response['_source']['INTRO']['UPLOAD_DATE'],'metadatadate' => $response['_source']['INTRO']['METADATA_DATE'], 'creationdate' => $response['_source']['INTRO']['CREATION_DATE'], 'authors' => $response['_source']['INTRO']['FILE_CREATOR'], 'files' => $files, 'mail' => $_SESSION['mail'], 'sampling_points' => $response['_source']['INTRO']['SAMPLING_POINT'], 'measurements' => $response['_source']['INTRO']['MEASUREMENT'], 'language' => $response['_source']['INTRO']['LANGUAGE'], 'fundings' => $response['_source']['INTRO']['FUNDINGS'], 'institutions' => $response['_source']['INTRO']['INSTITUTION'], 'scientific_field' => $response['_source']['INTRO']['SCIENTIFIC_FIELD'], 'sampling_date' => $response['_source']['INTRO']['SAMPLING_DATE'], 'sample_kinds' => $response['_source']['INTRO']['SAMPLE_KIND'], 'keywords' => $response['_source']['INTRO']['KEYWORDS'], 'license' => $response['_source']['INTRO']['LICENSE'], 'name_CSRF' => $name, 'value_CSRF' => $value]);
    }
})->setName('record')
    ->add($container->get('csrf'));

//Route affichant le formulaire d'edition dun dataset
$app->get('/editrecord', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    $request = new RequestApi();
    $status = $request->Check_status_datacite();
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf = $req->getAttribute($nameKey);
    $valuecsrf = $req->getAttribute($valueKey);
    $doi_already_exist = $request->Check_if_DOI_exist();
    if ($status == 200 && $doi_already_exist==false) {
        $id = $req->getparam('id');
        $response = $request->get_info_for_dataset($id);
        if ($response == false) {
            return $responseSlim->withRedirect('accueil');
        }
        elseif ($response['_source']['DATA'] == null) {
            return $responseSlim->withRedirect('accueil');
        }
        else {
            $found = "false";
            foreach ($response["_source"]["INTRO"]["FILE_CREATOR"] as $key => $value) {
                if (@$_SESSION["mail"] == $value["MAIL"] or $_SESSION['admin'] == 1) {
                    $found = "true";
                }
            }
            if ($found == "true") {

                $value = $response['_source']['INTRO']['LICENSE'];
                if ($value == "Creative commons Attribution alone") {
                    $license = 1;
                }
                elseif ($value == "Creative commons Attribution + ShareAlike") {
                    $license = 2;
                }
                elseif ($value == "Creative commons Attribution + Noncommercial") {
                    $license = 3;
                }
                elseif ($value == "Creative commons Attribution + NoDerivatives") {
                    $license = 4;
                }
                elseif ($value == "Creative commons Attribution + Noncommercial + ShareAlike") {
                    $license = 5;
                }
                elseif ($value == "Creative commons Attribution + Noncommercial + NoDerivatives") {
                    $license = 6;
                }

                return @$twig->render('edit_dataset.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'admin' => $_SESSION['admin'], 'doi' => $id, 'title' => $response['_source']['INTRO']['TITLE'], 'description' => $response['_source']['INTRO']['DATA_DESCRIPTION'], 'creation_date' => $response['_source']['INTRO']['CREATION_DATE'], 'sampling_dates' => $response['_source']['INTRO']['SAMPLING_DATE'], 'authors' => $response['_source']['INTRO']['FILE_CREATOR'], 'keywords' => $response['_source']['INTRO']['KEYWORDS'], 'sample_kinds' => $response['_source']['INTRO']['SAMPLE_KIND'], 'scientific_fields' => $response['_source']['INTRO']['SCIENTIFIC_FIELD'], 'institutions' => $response['_source']['INTRO']['INSTITUTION'], 'language' => $response['_source']['INTRO']['LANGUAGE'], 'sampling_points' => $response['_source']['INTRO']['SAMPLING_POINT'], 'measurements' => $response['_source']['INTRO']['MEASUREMENT'], 'license' => $license, 'publisher' => $response['_source']['INTRO']['PUBLISHER'], 'fundings' => $response['_source']['INTRO']['FUNDINGS'], 'accessright' => $response['_source']['INTRO']['ACCESS_RIGHT'], 'embargoed_date' => $response['_source']['INTRO']['PUBLICATION_DATE'], 'files' => $response['_source']['DATA']['FILES'], 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf]);
            }

            else {
                return $responseSlim->withRedirect('accueil');
            }
        }
    }
    else {
        echo $twig->render('error_datacite.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
    }
})->add($container->get('csrf'));

//Route receptionnant les données POST de l'edition
$app->post('/editrecord', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig = new Twig_Environment($loader);
    $Datasheet = new Datasheet();
    $doi = $req->getparam('id');
    $request = new RequestApi();
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf = $req->getAttribute($nameKey);
    $valuecsrf = $req->getAttribute($valueKey);
    $response = $request->get_info_for_dataset($doi, "Restricted");
    $collection = $response['_type'];
    $doi = $response['_id'];
    $db = $Datasheet->connect_tomongo();
    $array = $Datasheet->Postprocessing($_POST, "Edit", $doi,$db,$collection);
    if (array_key_exists('error', $array)) {
        $value = $response['_source']['INTRO']['LICENSE'];
        if ($value == "Creative commons Attribution alone") {
            $license = 1;
        }
        elseif ($value == "Creative commons Attribution + ShareAlike") {
            $license = 2;
        }
        elseif ($value == "Creative commons Attribution + Noncommercial") {
            $license = 3;
        }
        elseif ($value == "Creative commons Attribution + NoDerivatives") {
            $license = 4;
        }
        elseif ($value == "Creative commons Attribution + Noncommercial + ShareAlike") {
            $license = 5;
        }
        elseif ($value == "Creative commons Attribution + Noncommercial + NoDerivatives") {
            $license = 6;
        }
        return @$twig->render('edit_dataset.html.twig', ['error' => $array['error'], 'name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'],'admin' => $_SESSION['admin'], 'doi' => $doi, 'title' => $response['_source']['INTRO']['TITLE'], 'description' => $response['_source']['INTRO']['DATA_DESCRIPTION'], 'creation_date' => $response['_source']['INTRO']['CREATION_DATE'], 'sampling_dates' => $response['_source']['INTRO']['SAMPLING_DATE'], 'authors' => $response['_source']['INTRO']['FILE_CREATOR'], 'keywords' => $response['_source']['INTRO']['KEYWORDS'], 'sample_kinds' => $response['_source']['INTRO']['SAMPLE_KIND'], 'scientific_fields' => $response['_source']['INTRO']['SCIENTIFIC_FIELD'], 'institutions' => $response['_source']['INTRO']['INSTITUTION'], 'language' => $response['_source']['INTRO']['LANGUAGE'], 'sampling_points' => $response['_source']['INTRO']['SAMPLING_POINT'], 'measurements' => $response['_source']['INTRO']['MEASUREMENT'], 'license' => $license, 'publisher' => $response['_source']['INTRO']['PUBLISHER'], 'fundings' => $response['_source']['INTRO']['FUNDINGS'], 'accessright' => $response['_source']['INTRO']['ACCESS_RIGHT'], 'embargoed_date' => $response['_source']['INTRO']['PUBLICATION_DATE'], 'files' => $response['_source']['DATA']['FILES'], 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf]);
    }
    else {

        return @$twig->render('editsuccess.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
    }
})->add($container->get('csrf'));

//Route effectuant la query
$app->post('/getinfo', function (Request $req, Response $responseSlim) {
    $request = new RequestApi();
    $query = $req->getparam('query');
    $response = $request->requestToAPI($query);
    return $response;
});

//Route permettant la suppression d'un dataset
$app->post('/remove', function (Request $req, Response $responseSlim, $args) {
    $Datasheet = new Datasheet();
    $request = new RequestApi();
    $doi = $req->getparam('id');
    $response = $request->get_info_for_dataset($doi, "Restricted");
    $collection = $response['_type'];
    $doi = $response['_id'];
    $state = $Datasheet->removeUnpublishedDatasheet($collection, $doi);
    if ($state == true) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('removesucess.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;

    }
    else {
        return $responseSlim->withStatus(403);

    }

})
    ->add($container->get('csrf'));

//Route permettant le telechargement
$app->get('/files/{doi}/{filename}', function (Request $req, Response $responseSlim, $args) {
    $request = new RequestApi();
    $doi = $args['doi'];
    $filename = $args['filename'];
    if (strstr($doi, 'ORDAR') !== false) {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        $response = $request->get_info_for_dataset($config["DOI_PREFIX"] . '/' . $doi, "Restricted");
    }
    else {
        $response = $request->get_info_for_dataset($doi, "Restricted");
    }
    $File = new File();
    $download = $File->download($doi, $filename, $response);
    if ($download == NULL or $download == false) {
        return $responseSlim->withStatus(403);
    }

});

//Route permettant d'effectuer une preview
$app->get('/preview/{doi}/{filename}', function (Request $req, Response $responseSlim, $args) {

    $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
    $request = new RequestApi();
    $doi = $args['doi'];
    $fulldoi = $config["DOI_PREFIX"] . "/" . $args['doi'];
    $filename = $args['filename'];
    if (strstr($doi, 'ORDAR') !== false) {
        $response = $request->get_info_for_dataset($fulldoi, "Restricted");
    }
    else {
        $response = $request->get_info_for_dataset($doi, "Restricted");
    }
    $File = new File();
    $download = $File->preview($doi, $filename, $response);
    if ($download == NULL or $download == false) {
        return $responseSlim->withStatus(403);
    }
   return $responseSlim->withHeader('Content-type', $download);
    
});

//Route receptionnant les donnees POST du formulaire de contact d'auteurs
$app->post('/contact_author', function (Request $req, Response $responseSlim) {
	if ($_SERVER['HTTP_REFERER'] != NULL){
	    $loader = new Twig_Loader_Filesystem('search/templates');
	    $twig = new Twig_Environment($loader);
	    $Datasheet = new Datasheet();
	    $request = new RequestApi();
	    $author_name = $req->getparam('author_name');
	    $author_firstname = $req->getparam('author_first_name');
	    $author_name = htmlspecialchars($author_name, ENT_QUOTES);
	    $author_firstname = htmlspecialchars($author_firstname, ENT_QUOTES);
	    $doi = $req->getparam('doi');
	    $doi = htmlspecialchars($doi, ENT_QUOTES);
	    $sendermail = $req->getparam('User-email');
	    $message = $req->getparam('User-message');
	    $object = $req->getparam('User-object');
	    $response = $request->get_info_for_dataset($doi, "Restricted");
	    $error = $Datasheet->Send_Mail_author($doi, $response, $author_name, $author_firstname, $object, $message, $sendermail);
	    echo $twig->render('contact_request.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'error' => $error]);
	}
	else{
		return $responseSlim->withStatus(403);
	}

});

//Route permettant une exportation en un format specifique
$app->get('/export/{format}', function (Request $req, Response $responseSlim, $args) {
    $id = $req->getparam('id');
    $format = $args['format'];
    $request = new RequestApi();
    $response = $request->get_info_for_dataset($id, "Unrestricted");
    $file = new File();
    if ($format == "datacite") {
        $file = $file->export_to_datacite_xml($response);
        if ($file == false) {
 		$loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig');
        }
        else {
            header("Content-type: text/xml");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            print $file->asXML();
        }

    }
    elseif ($format == "json") {
        $file = $file->export_to_datacite_xml($response);
        $file = json_decode(json_encode($file) , 1);
        print json_encode($file);

    }
    elseif ($format == "bibtex") {
        $file = $file->export_to_Bibtex($response);
        if ($file == false) {
 		$loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig');
        }
        else {
            print $file;
            header("Content-type: text");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        }

    }
    elseif ($format == "dublincore") {
        $file = $file->export_to_dublincore_xml($response);
        if ($file == false) {
 		$loader = new Twig_Loader_Filesystem('search/templates');
        $twig = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig');
        }
        else {
            header("Content-type: text/xml");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            print $file->asXML();
        }

    }
    return @$responseSlim->withBody();

});

$app->run();

