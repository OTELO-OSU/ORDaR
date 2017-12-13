<?php

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \search\controller\DatasheetController as Datasheet;
use \search\controller\FileController as File;
use \search\controller\MailerController as Mailer;
use \search\controller\RequestController as RequestApi;
use \search\controller\UserController as User;
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Paris');

require '../vendor/autoload.php';

$c                 = new \Slim\Container();
$app               = new \Slim\App($c);
$container         = $app->getContainer();
$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard;
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        $loader  = new Twig_Loader_Filesystem('search/templates');
        $twig    = new Twig_Environment($loader);
        echo $twig->render('forbidden.html.twig');
    });
    return $guard;
};

$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig');
    };
};

$c['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        echo $twig->render('forbidden.html.twig');
    };
};

$mw = function ($request, $response, $next) {
    $file = file_exists($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
    if ($file == false) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $render = $twig->render('notfound.html.twig');
    } else {
        $file   = new File();
        $config = $file->ConfigFile();
        if (strlen($config['REPOSITORY_NAME']) == 0 or strlen($config['host']) == 0 or strlen($config['authSource']) == 0 or strlen($config['DOI_PREFIX']) == 0) {
            $loader = new Twig_Loader_Filesystem('search/templates');
            $twig   = new Twig_Environment($loader);
            $render = $twig->render('notfound.html.twig');
        }
    }
    if (!empty($render)) {
        $response->write($render);
        return $response;
    } else {
        $response = $next($request, $response);
        return $response;
    }

};

$check_current_user = function ($request, $response, $next) {
    $user      = new User();
    $checkuser = $user->check_current_user($_SESSION['mail']);
    if ($checkuser) {
        $response = $next($request, $response);
        return $response;

    } else {
        session_destroy();
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $render = $twig->render('notfound.html.twig');
        $response->write($render);
        return $response;

    }

};

session_start();

$app->get('/error-401', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    echo $twig->render('unauthorised.html.twig');
    return $responseSlim->withStatus(401);
});

//Route permettant d'acceder a l'accueil
$app->get('/', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader                   = new Twig_Loader_Filesystem('search/templates');
    $twig                     = new Twig_Environment($loader);
    if (@$_SESSION['name']) {
        echo $twig->render('accueil.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);
    } else {

        echo $twig->render('accueil.html.twig');
    }
})->add($mw);

//Route permettant d'acceder à l'accueil
$app->get('/accueil', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader                   = new Twig_Loader_Filesystem('search/templates');
    $twig                     = new Twig_Environment($loader);
    if (@$_SESSION['name']) {
        echo $twig->render('accueil.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);
    } else {
        echo $twig->render('accueil.html.twig');

    }
})->add($mw);

//Route permettant d'acceder a la page about
$app->get('/about', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    echo $twig->render('about.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
});
//Route permettant d'acceder a la page terms of use
$app->get('/terms', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    echo $twig->render('terms.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
});

//Route receptionnant les données POST contact
$app->post('/contact', function (Request $req, Response $responseSlim) {
    if ($_SERVER['HTTP_REFERER'] != null) {
        $loader     = new Twig_Loader_Filesystem('search/templates');
        $twig       = new Twig_Environment($loader);
        $sendermail = $req->getparam('User-email');
        $message    = $req->getparam('User-message');
        $object     = $req->getparam('User-object');
        $request    = new RequestApi();
        $Mail       = new Mailer();
        $error      = $Mail->Send_Contact_Mail($object, $message, $sendermail);
        echo $twig->render('contact_request.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'error' => $error]);
    } else {
        return $responseSlim->withStatus(403);
    }
});

//Route affichant les resultats
$app->get('/searchresult', function (Request $req, Response $responseSlim) {
    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader                   = new Twig_Loader_Filesystem('search/templates');
    $twig                     = new Twig_Environment($loader);
    $query                    = $req->getparam('query');

    if (@$_SESSION['name']) {
        echo $twig->render('accueil.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'query' => $query]);
    } else {
        echo $twig->render('accueil.html.twig', ['query' => $query]);
    }
})->add($mw);

//Route permettant la connexion d'un utilisateur
$app->get('/login', function (Request $req, Response $responseSlim) {

    // $file = new File();
    //$config=$file->ConfigFile();

    /* $_SESSION['name'] = $_SERVER['HTTP_SN'];
    $_SESSION['firstname'] = $_SERVER['HTTP_GIVENNAME'];
    $_SESSION['mail'] = $_SERVER['HTTP_MAIL'];
    $_SESSION['admin'] = "0";*/

    /*foreach ($config["admin"] as $key => $value) {
    $array = explode(",", $value);
    }
    foreach ($array as $key => $value) {
    if ($value == $_SESSION['mail']) {
    $_SESSION['admin'] = "1";
    }

    }*/

    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    echo $twig->render('login.html.twig');

    session_regenerate_id();

    /*if ($_SESSION['HTTP_REFERER']) {
return $responseSlim->withRedirect($_SESSION['HTTP_REFERER']);
}
else {
return $responseSlim->withRedirect('accueil');
}*/

})->add($mw);

$app->post('/login', function (Request $req, Response $responseSlim) {
    $loader   = new Twig_Loader_Filesystem('search/templates');
    $twig     = new Twig_Environment($loader);
    $mail     = $req->getparam('email');
    $password = $req->getparam('password');
    $user     = new User();
    $error    = $user->login($mail, $password);
    if (!$error) {
        return $responseSlim->withRedirect('accueil');
    } else {
        echo $twig->render('login.html.twig', ['error' => $error]);
    }

});

$app->get('/loginCAS', function (Request $req, Response $responseSlim) {
    $user      = new User();
    $checkuser = $user->check_current_user($_SERVER['HTTP_MAIL']);
    if ($checkuser) {
        $_SESSION['name']      = $checkuser->name;
        $_SESSION['firstname'] = $checkuser->firstname;
        $_SESSION['mail']      = $checkuser->mail;
        $_SESSION['admin']     = $checkuser->type;
        return $responseSlim->withRedirect('accueil');
    } else {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $error  = "No account linked to this email! Please register";
        echo $twig->render('login.html.twig', ['error' => $error]);
    }
});

//Route permettant l'inscription 'd'un utilisateur
$app->get('/signup', function (Request $req, Response $responseSlim) {
    if (!@$_SESSION['name']) {
        $nameKey = $this
            ->csrf
            ->getTokenNameKey();
        $valueKey = $this
            ->csrf
            ->getTokenValueKey();
        $namecsrf  = $req->getAttribute($nameKey);
        $valuecsrf = $req->getAttribute($valueKey);
        $code            = $req->getparam('code');
        if ($code) {

            $request    = new RequestApi();
            $orcid= $request->get_ORCID_ID($code,"signup");
            $orcid=json_decode($orcid,true);
            
        }
        $loader    = new Twig_Loader_Filesystem('search/templates');
        $twig      = new Twig_Environment($loader);
        echo $twig->render('signup.html.twig', ['name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf,'orcid' => $orcid['orcid'],]);
    } else {
        return $responseSlim->withRedirect('accueil');

    }

})->add($mw)->add($container->get('csrf'));

$app->post('/signup', function (Request $req, Response $responseSlim) {
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf        = $req->getAttribute($nameKey);
    $valuecsrf       = $req->getAttribute($valueKey);
    $loader          = new Twig_Loader_Filesystem('search/templates');
    $twig            = new Twig_Environment($loader);
    $name            = $req->getparam('name');
    $firstname       = $req->getparam('firstname');
    $mail            = $req->getparam('email');
    $password        = $req->getparam('password');
    $passwordconfirm = $req->getparam('password_confirm');
    $user            = new User();
    $error           = $user->signup($name, $firstname, $mail, $password, $passwordconfirm);
    if (!$error) {
        return $responseSlim->withRedirect('accueil');
    } else {
        echo $twig->render('signup.html.twig', ['error' => $error, 'name_CSRF' => $nameKey, 'value_CSRF' => $valueKey]);
    }

})->add($container->get('csrf'));

$app->get('/recover', function (Request $req, Response $responseSlim) {
    if (!@$_SESSION['name']) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $token  = $req->getparam('token');
        if ($token) {
            $user   = new User();
            $result = $user->check_token($token);
            if ($result == false) {
                return $responseSlim->withRedirect('accueil');
            } else {
                echo $twig->render('change_password.html.twig', ['token' => $token]);
            }
        } else {
            echo $twig->render('recover.html.twig');
        }
    } else {
        return $responseSlim->withRedirect('accueil');

    }
})->add($mw);

$app->post('/recover', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    $mail   = $req->getparam('email');
    $user   = new User();
    $error  = $user->recover_send_mail($mail);
    echo $twig->render('recover.html.twig', ['error' => $error, 'post' => 'true']);

})->add($mw);

$app->post('/change_password', function (Request $req, Response $responseSlim) {
    $loader          = new Twig_Loader_Filesystem('search/templates');
    $twig            = new Twig_Environment($loader);
    $token           = $req->getparam('token');
    $password        = $req->getparam('password');
    $passwordconfirm = $req->getparam('password_confirm');
    $user            = new User();
    $error           = $user->change_password($token, $password, $passwordconfirm);
    echo $twig->render('change_password.html.twig', ['error' => $error, 'token' => $token, 'post' => 'true']);
})->add($mw);

$app->get('/activate_account', function (Request $req, Response $responseSlim) {
    if (!@$_SESSION['name']) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $token  = $req->getparam('token');
        if ($token) {
            $user  = new User();
            $error = $user->activate_account($token);
            if ($error == false) {
                return $responseSlim->withRedirect('accueil');
            } else {
                echo $twig->render('validation_mail.html.twig');

            }

        } else {
            return $responseSlim->withRedirect('accueil');

        }
    } else {
        return $responseSlim->withRedirect('accueil');

    }
})->add($mw);

$app->get('/myaccount', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['name']) {
        $loader  = new Twig_Loader_Filesystem('search/templates');
        $twig    = new Twig_Environment($loader);
        $nameKey = $this
            ->csrf
            ->getTokenNameKey();
        $valueKey = $this
            ->csrf
            ->getTokenValueKey();
        $namecsrf  = $req->getAttribute($nameKey);
        $valuecsrf = $req->getAttribute($valueKey);
        $user      = new User();
        $code            = $req->getparam('code');
        $user      = $user->getUserInfo($_SESSION['mail']);
            $orcid=$user[0]->ORCID_ID;
        if ($code) {
            $request    = new RequestApi();
            $orcid= $request->get_ORCID_ID($code,'myaccount');
            $orcid=json_decode($orcid,true);
            $orcid=$orcid['orcid'];
        }
        
        echo $twig->render('myaccount.html.twig', ['name' => $user[0]->name, 'firstname' => $user[0]->firstname,'orcid' => $orcid, 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf, 'mail' => $_SESSION['mail'], 'admin' => $_SESSION['admin']]);
    } else {
        return $responseSlim->withRedirect('accueil');
    }
})->add($mw)->add($container->get('csrf'))->add($check_current_user);

$app->post('/myaccount', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['name']) {
        $loader  = new Twig_Loader_Filesystem('search/templates');
        $twig    = new Twig_Environment($loader);
        $nameKey = $this
            ->csrf
            ->getTokenNameKey();
        $valueKey = $this
            ->csrf
            ->getTokenValueKey();
        $namecsrf  = $req->getAttribute($nameKey);
        $valuecsrf = $req->getAttribute($valueKey);
        $name      = $req->getparam('name');
        $firstname = $req->getparam('firstname');
        $ORCID_ID = $req->getparam('orcid');
        $user      = new User();
        $user->setUserInfo($_SESSION['mail'], $name, $firstname,$ORCID_ID);
        $user = $user->getUserInfo($_SESSION['mail']);
                echo $twig->render('myaccount.html.twig', ['message'=> "Account updated successfully",'name' => $user[0]->name, 'firstname' => $user[0]->firstname,'orcid' => $ORCID_ID, 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf, 'mail' => $_SESSION['mail'], 'admin' => $_SESSION['admin']]);

    }
})->add($mw)->add($container->get('csrf'));

$app->post('/resetpassword', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    $mail   = $_SESSION['mail'];
    $user   = new User();
    $error  = $user->recover_send_mail($mail);
    session_destroy();
    echo $twig->render('recover.html.twig', ['error' => $error, 'post' => 'true']);

})->add($mw)->add($container->get('csrf'));

$app->get('/listusers', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['admin'] == 1) {
        $loader  = new Twig_Loader_Filesystem('search/templates');
        $twig    = new Twig_Environment($loader);
        $nameKey = $this
            ->csrf
            ->getTokenNameKey();
        $valueKey = $this
            ->csrf
            ->getTokenValueKey();
        $namecsrf      = $req->getAttribute($nameKey);
        $valuecsrf     = $req->getAttribute($valueKey);
        $user          = new User();
        $usersapproved = $user->getAllUsersApproved();
        $userswaiting  = $user->getAllUsersWaiting();
        echo $twig->render('listusers.html.twig', ['usersapproved' => $usersapproved, 'userswaiting' => $userswaiting, 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf, 'mail' => $_SESSION['mail']]);
    } else {
        return $responseSlim->withRedirect('accueil');
    }
})->add($mw)->add($container->get('csrf'))->add($check_current_user);

$app->post('/approveuser', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['admin'] == 1) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $email  = $req->getparam('email');
        $user   = new User();
        $error  = $user->approveUser($email);
        return $responseSlim->withRedirect('listusers');
    }

})->add($mw)->add($container->get('csrf'));

$app->post('/disableuser', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['admin'] == 1) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $email  = $req->getparam('email');
        $user   = new User();
        $error  = $user->disableUser($email);
        return $responseSlim->withRedirect('listusers');
    }

})->add($mw)->add($container->get('csrf'));

$app->post('/removeuser', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['admin'] == 1) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        $email  = $req->getparam('email');
        $user   = new User();
        $error  = $user->deleteUser($email);
        return $responseSlim->withRedirect('listusers');
    }

})->add($mw)->add($container->get('csrf'));

$app->post('/modifyuser', function (Request $req, Response $responseSlim) {
    if (@$_SESSION['admin'] == 1) {
        $loader    = new Twig_Loader_Filesystem('search/templates');
        $twig      = new Twig_Environment($loader);
        $email     = $req->getparam('email');
        $name      = $req->getparam('name');
        $firstname = $req->getparam('firstname');
        $type      = $req->getparam('type');
        if ($type == "on") {
            $type = 1;
        } else {
            $type = 0;
        }
        $user  = new User();
        $error = $user->modifyUser($email, $name, $firstname, $type);
        return $responseSlim->withRedirect('listusers');
    }

})->add($mw)->add($container->get('csrf'));

//Route permettant la deconnexion d'un utilisateur
$app->get('/logout', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    session_destroy();
    $file   = new File();
    $config = $file->ConfigFile();
    return $responseSlim->withRedirect($config['REPOSITORY_URL'] . '/Shibboleth.sso/Logout?return=' . $config['REPOSITORY_URL']);

})->add($mw);

//Route affichant les publication de l'utilisateur connecté
$app->get('/mypublications', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    if (@$_SESSION['name']) {
        echo $twig->render('mypublications.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);
    } else {
        return $responseSlim->withRedirect('accueil');
    }

})->add($mw)->add($check_current_user);

//Route affichant le formulaire d'upload
$app->get('/upload', function (Request $req, Response $responseSlim) {
    $loader = new Twig_Loader_Filesystem('search/templates');
    $twig   = new Twig_Environment($loader);
    if (@$_SESSION['name']) {
        $nameKey = $this
            ->csrf
            ->getTokenNameKey();
        $valueKey = $this
            ->csrf
            ->getTokenValueKey();
        $name              = $req->getAttribute($nameKey);
        $value             = $req->getAttribute($valueKey);
        $request           = new RequestApi();
        $dataset           = new Datasheet();
        $status            = $request->Check_status_datacite();
        $doi_already_exist = $request->Check_if_DOI_exist();
        if ($status == 200 && $doi_already_exist == false) {
            echo $twig->render('upload.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'name_CSRF' => $name, 'value_CSRF' => $value]);
        } else {
            echo $twig->render('error_datacite.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
        }

    } else {
        return $responseSlim->withRedirect('accueil');
    }
})
    ->setName('upload')->add($mw)
    ->add($container->get('csrf'))->add($check_current_user);

//Route receptionnant les données POST de l'upload
$app->post('/upload', function (Request $req, Response $responseSlim) {
    $loader  = new Twig_Loader_Filesystem('search/templates');
    $twig    = new Twig_Environment($loader);
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf  = $req->getAttribute($nameKey);
    $valuecsrf = $req->getAttribute($valueKey);
    $Datasheet = new Datasheet();
    $db        = $Datasheet->connect_tomongo();
    $response  = $Datasheet->Postprocessing($_POST, "Upload", "0", $db, 'Manual_Depot');

    if (isset($response['error'])) {
        $value = $response['dataform']['LICENSE'];
        if ($value == "Creative commons Attribution alone") {
            $license = 1;
        } elseif ($value == "Creative commons Attribution + ShareAlike") {
            $license = 2;
        } elseif ($value == "Creative commons Attribution + Noncommercial") {
            $license = 3;
        } elseif ($value == "Creative commons Attribution + NoDerivatives") {
            $license = 4;
        } elseif ($value == "Creative commons Attribution + Noncommercial + ShareAlike") {
            $license = 5;
        } elseif ($value == "Creative commons Attribution + Noncommercial + NoDerivatives") {
            $license = 6;
        }
        echo $twig->render('upload.html.twig', ['error' => $response['error'], 'name' => $_SESSION['name'], 'mail' => $_SESSION['mail'], 'firstname' => $_SESSION['firstname'], 'title' => $response['dataform']['TITLE'], 'description' => $response['dataform']['DATA_DESCRIPTION'], 'creation_date' => $response['dataform']['CREATION_DATE'], 'sampling_dates' => $response['dataform']['SAMPLING_DATE'], 'authors' => $response['dataform']['FILE_CREATOR'], 'keywords' => $response['dataform']['KEYWORDS'], 'sample_kinds' => $response['dataform']['SAMPLE_KIND'], 'scientific_fields' => $response['dataform']['SCIENTIFIC_FIELD'], 'institutions' => $response['dataform']['INSTITUTION'], 'language' => $response['dataform']['LANGUAGE'], 'sampling_points' => $response['dataform']['SAMPLING_POINT'], 'measurements' => $response['dataform']['MEASUREMENT'], 'license' => $license, 'publisher' => $response['dataform']['PUBLISHER'], 'fundings' => $response['dataform']['FUNDINGS'], 'accessright' => $response['dataform']['ACCESS_RIGHT'], 'embargoed_date' => $response['dataform']['PUBLICATION_DATE'], 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf]);
    } else {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        echo $twig->render('display_actions.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'message' => $response]);

    }
    //return $response;
    //return $responseSlim->withRedirect('upload');

})->add($container->get('csrf'));

//Route receptionnant les données POST mypublications
$app->post('/getmypublications', function (Request $req, Response $responseSlim) {
    $request = new RequestApi();
    if ($_SESSION['admin'] == "1") {
        $query    = $req->getparam('query');
        $from     = $req->getparam('from');
        $response = $request->requestToAPIAdmin($query, $from);
        return $response;
    } else {
        if (@$_SESSION['name']) {
            $authors_mail = $_SESSION['mail'];
            $authors_name = $_SESSION['name'];
            $query        = $req->getparam('query');
            $from         = $req->getparam('from');

            if (!empty($query)) {
                $response = $request->getPublicationsofUser($authors_mail, $authors_name, $query, $from);
            } else {
                $response = $request->getPublicationsofUser($authors_mail, $authors_name, "null", $from);
            }
            return $response;
        } else {
            return $responseSlim->withRedirect('accueil');
        }
    }
});

//Route affichant les details d'un dataset
$app->get('/record', function (Request $req, Response $responseSlim) {
    $file   = new File();
    $config = $file->ConfigFile();

    $_SESSION['HTTP_REFERER'] = $_SERVER['REQUEST_URI'];
    $loader                   = new Twig_Loader_Filesystem('search/templates');
    $twig                     = new Twig_Environment($loader);
    $request                  = new RequestApi();
    $id                       = $req->getparam('id');
    $nameKey                  = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $name     = $req->getAttribute($nameKey);
    $value    = $req->getAttribute($valueKey);
    $response = $request->get_info_for_dataset($id);
    if (isset($response['_source']['DATA'])) {
        $files = $response['_source']['DATA']['FILES'];
    } else {
        $files = null;
    }
    if ($response == false) {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        echo $twig->render('notfound.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;

    } else {
        if (strstr($id, $config['REPOSITORY_NAME']) !== false) {
            $id = split("/", $response['_id']);
            $id = $id[1];
        } else {
            $id = $id;
        }

        return @$twig->render('viewdatadetails.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'doi' => $response['_id'], 'admin' => $_SESSION['admin'], 'id' => $id, 'title' => $response['_source']['INTRO']['TITLE'], 'datadescription' => nl2br($response['_source']['INTRO']['DATA_DESCRIPTION']), 'accessright' => $response['_source']['INTRO']['ACCESS_RIGHT'], 'publicationdate' => $response['_source']['INTRO']['PUBLICATION_DATE'], 'uploaddate' => $response['_source']['INTRO']['UPLOAD_DATE'], 'metadatadate' => $response['_source']['INTRO']['METADATA_DATE'], 'creationdate' => $response['_source']['INTRO']['CREATION_DATE'], 'authors' => $response['_source']['INTRO']['FILE_CREATOR'], 'files' => $files, 'mail' => $_SESSION['mail'], 'sampling_points' => $response['_source']['INTRO']['SAMPLING_POINT'], 'measurements' => $response['_source']['INTRO']['MEASUREMENT'], 'methodology' => $response['_source']['INTRO']['METHODOLOGY'], 'acronym' => $response['_source']['INTRO']['ACRONYM'], 'language' => $response['_source']['INTRO']['LANGUAGE'], 'fundings' => $response['_source']['INTRO']['FUNDINGS'], 'institutions' => $response['_source']['INTRO']['INSTITUTION'], 'scientific_field' => $response['_source']['INTRO']['SCIENTIFIC_FIELD'], 'supplementary_fields' => $response['_source']['INTRO']['SUPPLEMENTARY_FIELDS'], 'sampling_date' => $response['_source']['INTRO']['SAMPLING_DATE'], 'sample_kinds' => $response['_source']['INTRO']['SAMPLE_KIND'], 'keywords' => $response['_source']['INTRO']['KEYWORDS'], 'license' => $response['_source']['INTRO']['LICENSE'], 'publisher' => $response['_source']['INTRO']['PUBLISHER'], 'referent' => $response['_source']['INTRO']['REFERENT'], 'name_CSRF' => $name, 'value_CSRF' => $value, 'REPOSITORY_NAME' => $config['REPOSITORY_NAME'], 'REPOSITORY_URL' => $config['REPOSITORY_URL'], 'SOCIAL_SHARING' => $config['SOCIAL_SHARING']]);
    }
})->setName('record')
    ->add($container->get('csrf'));

//Route affichant le formulaire d'edition dun dataset
$app->get('/editrecord', function (Request $req, Response $responseSlim) {
    $loader  = new Twig_Loader_Filesystem('search/templates');
    $twig    = new Twig_Environment($loader);
    $request = new RequestApi();
    $status  = $request->Check_status_datacite();
    $nameKey = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf          = $req->getAttribute($nameKey);
    $valuecsrf         = $req->getAttribute($valueKey);
    $doi_already_exist = $request->Check_if_DOI_exist();
    if ($status == 200 && $doi_already_exist == false) {
        $id       = $req->getparam('id');
        $response = $request->get_info_for_dataset($id);
        if ($response == false) {
            return $responseSlim->withRedirect('accueil');
        } elseif ($response['_source']['DATA'] == null) {
            return $responseSlim->withRedirect('accueil');
        } else {
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
                } elseif ($value == "Creative commons Attribution + ShareAlike") {
                    $license = 2;
                } elseif ($value == "Creative commons Attribution + Noncommercial") {
                    $license = 3;
                } elseif ($value == "Creative commons Attribution + NoDerivatives") {
                    $license = 4;
                } elseif ($value == "Creative commons Attribution + Noncommercial + ShareAlike") {
                    $license = 5;
                } elseif ($value == "Creative commons Attribution + Noncommercial + NoDerivatives") {
                    $license = 6;
                }

                return @$twig->render('edit_dataset.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'admin' => $_SESSION['admin'], 'doi' => $id, 'title' => $response['_source']['INTRO']['TITLE'], 'description' => $response['_source']['INTRO']['DATA_DESCRIPTION'], 'creation_date' => $response['_source']['INTRO']['CREATION_DATE'], 'sampling_dates' => $response['_source']['INTRO']['SAMPLING_DATE'], 'authors' => $response['_source']['INTRO']['FILE_CREATOR'], 'keywords' => $response['_source']['INTRO']['KEYWORDS'], 'sample_kinds' => $response['_source']['INTRO']['SAMPLE_KIND'], 'scientific_fields' => $response['_source']['INTRO']['SCIENTIFIC_FIELD'], 'institutions' => $response['_source']['INTRO']['INSTITUTION'], 'language' => $response['_source']['INTRO']['LANGUAGE'], 'methodology' => $response['_source']['INTRO']['METHODOLOGY'], 'acronym' => $response['_source']['INTRO']['ACRONYM'], 'sampling_points' => $response['_source']['INTRO']['SAMPLING_POINT'], 'measurements' => $response['_source']['INTRO']['MEASUREMENT'], 'supplementary_fields' => $response['_source']['INTRO']['SUPPLEMENTARY_FIELDS'], 'license' => $license, 'publisher' => $response['_source']['INTRO']['PUBLISHER'], 'fundings' => $response['_source']['INTRO']['FUNDINGS'], 'referent' => $response['_source']['INTRO']['REFERENT'], 'accessright' => $response['_source']['INTRO']['ACCESS_RIGHT'], 'embargoed_date' => $response['_source']['INTRO']['PUBLICATION_DATE'], 'files' => $response['_source']['DATA']['FILES'], 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf]);
            } else {
                return $responseSlim->withRedirect('accueil');
            }
        }
    } else {
        echo $twig->render('error_datacite.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail']]);;
    }
})->add($container->get('csrf'));

//Route receptionnant les données POST de l'edition
$app->post('/editrecord', function (Request $req, Response $responseSlim) {
    $loader    = new Twig_Loader_Filesystem('search/templates');
    $twig      = new Twig_Environment($loader);
    $Datasheet = new Datasheet();
    $doi       = $req->getparam('id');
    $request   = new RequestApi();
    $nameKey   = $this
        ->csrf
        ->getTokenNameKey();
    $valueKey = $this
        ->csrf
        ->getTokenValueKey();
    $namecsrf   = $req->getAttribute($nameKey);
    $valuecsrf  = $req->getAttribute($valueKey);
    $response   = $request->get_info_for_dataset($doi);
    $collection = $response['_type'];
    $doi        = $response['_id'];
    $db         = $Datasheet->connect_tomongo();
    $array      = $Datasheet->Postprocessing($_POST, "Edit", $doi, $db, $collection);
    if (array_key_exists('error', $array)) {
        if ($array['error'] == "Dont exist") {
            $loader = new Twig_Loader_Filesystem('search/templates');
            $twig   = new Twig_Environment($loader);
            return $twig->render('notfound.html.twig');
        }
        $value = $response['_source']['INTRO']['LICENSE'];
        if ($value == "Creative commons Attribution alone") {
            $license = 1;
        } elseif ($value == "Creative commons Attribution + ShareAlike") {
            $license = 2;
        } elseif ($value == "Creative commons Attribution + Noncommercial") {
            $license = 3;
        } elseif ($value == "Creative commons Attribution + NoDerivatives") {
            $license = 4;
        } elseif ($value == "Creative commons Attribution + Noncommercial + ShareAlike") {
            $license = 5;
        } elseif ($value == "Creative commons Attribution + Noncommercial + NoDerivatives") {
            $license = 6;
        }
        return @$twig->render('edit_dataset.html.twig', ['error' => $array['error'], 'name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'admin' => $_SESSION['admin'], 'doi' => $doi, 'title' => $response['_source']['INTRO']['TITLE'], 'description' => $response['_source']['INTRO']['DATA_DESCRIPTION'], 'creation_date' => $response['_source']['INTRO']['CREATION_DATE'], 'sampling_dates' => $response['_source']['INTRO']['SAMPLING_DATE'], 'authors' => $response['_source']['INTRO']['FILE_CREATOR'], 'keywords' => $response['_source']['INTRO']['KEYWORDS'], 'sample_kinds' => $response['_source']['INTRO']['SAMPLE_KIND'], 'scientific_fields' => $response['_source']['INTRO']['SCIENTIFIC_FIELD'], 'institutions' => $response['_source']['INTRO']['INSTITUTION'], 'language' => $response['_source']['INTRO']['LANGUAGE'], 'sampling_points' => $response['_source']['INTRO']['SAMPLING_POINT'], 'methodology' => $response['_source']['INTRO']['METHODOLOGY'], 'acronym' => $response['_source']['INTRO']['ACRONYM'], 'measurements' => $response['_source']['INTRO']['MEASUREMENT'], 'supplementary_fields' => $response['_source']['INTRO']['SUPPLEMENTARY_FIELDS'], 'license' => $license, 'publisher' => $response['_source']['INTRO']['PUBLISHER'], 'fundings' => $response['_source']['INTRO']['FUNDINGS'], 'accessright' => $response['_source']['INTRO']['ACCESS_RIGHT'], 'referent' => $response['_source']['INTRO']['REFERENT'], 'embargoed_date' => $response['_source']['INTRO']['PUBLICATION_DATE'], 'files' => $response['_source']['DATA']['FILES'], 'name_CSRF' => $namecsrf, 'value_CSRF' => $valuecsrf]);
    } else {

        return @$twig->render('display_actions.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'message' => $array]);

    }
})->add($container->get('csrf'));

//Route effectuant la query
$app->post('/getinfo', function (Request $req, Response $responseSlim) {
    $request  = new RequestApi();
    $query    = $req->getparam('query');
    $from     = $req->getparam('from');
    $response = $request->requestToAPI($query, $from);
    return $response;
});

//Route permettant la suppression d'un dataset
$app->post('/remove', function (Request $req, Response $responseSlim, $args) {
    $Datasheet  = new Datasheet();
    $request    = new RequestApi();
    $doi        = $req->getparam('id');
    $response   = $request->get_info_for_dataset($doi);
    $collection = $response['_type'];
    $doi        = $response['_id'];
    $state      = $Datasheet->removeUnpublishedDatasheet($collection, $doi);
    if ($state == "fail_ssh") {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        echo $twig->render('display_actions.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'message' => '   <div class="ui message red"  style="display: block;">Removing is not available, please try again later!</div>']);;
    } elseif ($state == "true") {
        $loader = new Twig_Loader_Filesystem('search/templates');
        $twig   = new Twig_Environment($loader);
        echo $twig->render('display_actions.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'message' => '   <div class="ui message green"  style="display: block;">Datasheet removed!</div>']);;

    } elseif ($state == "false") {

        return $responseSlim->withStatus(403);

    }

})
    ->add($container->get('csrf'));

//Route permettant le telechargement
$app->get('/files/{doi}/{filename}', function (Request $req, Response $responseSlim, $args) {
    $file   = new File();
    $config = $file->ConfigFile();

    $request  = new RequestApi();
    $doi      = $args['doi'];
    $filename = $args['filename'];
    if (strstr($doi, $config['REPOSITORY_NAME']) !== false) {
        $response = $request->get_info_for_dataset($config["DOI_PREFIX"] . '/' . $doi);
    } else {
        $response = $request->get_info_for_dataset($doi);
    }
    $File     = new File();
    $download = $File->download($doi, $filename, $response);
    if ($download == null or $download == false) {
        return $responseSlim->withStatus(403);
    }

});

//Route permettant d'effectuer une preview
$app->get('/preview/{doi}/{filename}', function (Request $req, Response $responseSlim, $args) {

    $file   = new File();
    $config = $file->ConfigFile();

    $request  = new RequestApi();
    $doi      = $args['doi'];
    $fulldoi  = $config["DOI_PREFIX"] . "/" . $args['doi'];
    $filename = $args['filename'];
    if (strstr($doi, $config['REPOSITORY_NAME']) !== false) {
        $response = $request->get_info_for_dataset($fulldoi);
    } else {
        $response = $request->get_info_for_dataset($doi);
    }
    $File     = new File();
    $download = $File->preview($doi, $filename, $response);
    if ($download == null or $download == false) {
        return $responseSlim->withStatus(403);
    }
    return $responseSlim->withHeader('Content-type', $download);

});

//Route permettant d'effectuer une preview du changelog
$app->get('/changelog', function (Request $req, Response $responseSlim, $args) {
    $File    = new File();
    $request = new RequestApi();

    $config   = $File->ConfigFile();
    $doi      = $req->getparam('id');
    $response = $request->get_info_for_dataset($doi);
    if (isset($response['_source']['DATA'])) {
        $changelog = $File->changelog($doi);
        print_r($changelog);
    }
});

//Route receptionnant les donnees POST du formulaire de contact d'auteurs
$app->post('/contact_author', function (Request $req, Response $responseSlim) {
    if ($_SERVER['HTTP_REFERER'] != null) {
        $loader           = new Twig_Loader_Filesystem('search/templates');
        $twig             = new Twig_Environment($loader);
        $Datasheet        = new Datasheet();
        $request          = new RequestApi();
        $Mail             = new Mailer();
        $author_name      = $req->getparam('author_name');
        $author_firstname = $req->getparam('author_first_name');
        $author_name      = htmlspecialchars($author_name, ENT_QUOTES);
        $author_firstname = htmlspecialchars($author_firstname, ENT_QUOTES);
        $doi              = $req->getparam('doi');
        $doi              = htmlspecialchars($doi, ENT_QUOTES);
        $sendermail       = $req->getparam('User-email');
        $message          = $req->getparam('User-message');
        $object           = $req->getparam('User-object');
        $response         = $request->get_info_for_dataset($doi);
        $error            = $Mail->Send_Mail_author($doi, $response, $author_name, $author_firstname, $object, $message, $sendermail);
        echo $twig->render('contact_request.html.twig', ['name' => $_SESSION['name'], 'firstname' => $_SESSION['firstname'], 'mail' => $_SESSION['mail'], 'error' => $error]);
    } else {
        return $responseSlim->withStatus(403);
    }

});

//Route permettant une exportation en un format specifique
$app->get('/export/{format}', function (Request $req, Response $responseSlim, $args) {
    $id       = $req->getparam('id');
    $format   = $args['format'];
    $request  = new RequestApi();
    $response = $request->get_info_for_dataset($id);
    $file     = new File();
    if ($format == "datacite") {
        $file = $file->export_to_datacite_xml($response);
        if ($file == false) {
            $loader = new Twig_Loader_Filesystem('search/templates');
            $twig   = new Twig_Environment($loader);
            echo $twig->render('notfound.html.twig');
        } else {
            header("Content-type: text/xml");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            print $file->asXML();
        }

    } elseif ($format == "json") {
        $file = $file->export_to_datacite_xml($response);
        $file = json_decode(json_encode($file), 1);
        print json_encode($file);

    } elseif ($format == "bibtex") {
        $file = $file->export_to_Bibtex($response);
        if ($file == false) {
            $loader = new Twig_Loader_Filesystem('search/templates');
            $twig   = new Twig_Environment($loader);
            echo $twig->render('notfound.html.twig');
        } else {
            print $file;
            header("Content-type: text");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        }

    } elseif ($format == "dublincore") {
        $file = $file->export_to_dublincore_xml($response);
        if ($file == false) {
            $loader = new Twig_Loader_Filesystem('search/templates');
            $twig   = new Twig_Environment($loader);
            echo $twig->render('notfound.html.twig');
        } else {
            header("Content-type: text/xml");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            print $file->asXML();
        }

    }
    return @$responseSlim->withBody();

});

$app->run();
