![Alt text](/Frontend/src/img/logo.png?raw=true)

# ORDaR Beta 1

Table of contents:
=================
* [Installation](#installation)
* [Organisation](#organisation)
* [Utilisation](#utilisation)
* [Docker](#docker)


# Installation  <a name="installation"></a>



**Prérequis :**

    -MongoDB 3.4.2

    -Elasticsearch 5.2

    -Mongo connector 

    -PHP 5.6

    -PHP-curl
    
    -PHP libssh2

    -Mongo php driver


Pour ubuntu 16.04(pour d’autre systèmes consulter le manuel de mongodb)

**Installation de mongodb :**

    sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
    echo "deb [ arch=amd64,arm64 ] http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
    sudo apt-get update
    sudo apt-get install -y mongodb-org



**Installation d’elasticsearch**

    Oracle JDK doit être installé avant de continuer.

    curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.2.2.tar.gz

    tar -xvf elasticsearch-5.2.2.tar.gz

    cd elasticsearch-5.2.2/bin


**Installation de mongo-connector**

    apt-get install python-pip
    pip install 'mongo-connector[elastic5]'

**Installation php :**

    sudo apt-get install  php5.6

    Installer php curl :

    sudo apt-get install php5.6-curl
    
    et php-mongo et phplibssh2

    sudo apt-get install php-mongo

    sudo apt-get install libssh2-1-dev 
   
    pecl install ssh2

    On active les extensions en ajoutant les lignes suivantes au php.ini

	extension=mongo.so
	extension=ssh2.so

Afin d'envoyer des mails, vous devez configurer un SMTP sur votre serveur.

    

**Configuration apache2**

    activer mode rewrite :
    sudo a2enmod rewrite

    Modifier la configuration apache:
    DocumentRoot /var/www/html/ORDaR/Frontend/src/


    <Directory "/var/www/html/ORDaR/Frontend/src/">
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>

**Demarrer la base mongo en mode replica set :**
    
    sudo mongod --replSet "rs0"

    Démarrer shell mongo et exécuter :
        rs.initiate()

    Se connecter sur la base admin:

        use admin

    Créer un utilisateur avec un rôle backup:

    db.createUser({user: "USER",pwd: "PASSWORD",roles: [ { role: "backup", db: "admin" } ]})

    Ensuite se connecter sur la base ORDaR et créer l'utilisateur qui pourra modifier les données:

        use ORDaR

        db.createUser({user: "USER",pwd: "PASSWORD",roles: [ { role: "readWrite", db: "ORDaR" } ]})

    Créer aussi une base DOI et créer l'utilisateur qui pourra modifier les données:

        use DOI

        db.createUser({user: "USER",pwd: "PASSWORD",roles: [ { role: "readWrite", db: "DOI" } ]})

Ensuite démarrer elasticsearch,
rendez vous dans le dossier précédemment téléchargé /bin et exécuter :
./elasticsearch

**Récupérer le projet :**

    git clone https://github.com/arnouldpy/ORDaR.git

    Rendez vous dans le dossier créé, une fois dans le dossier Ordar, exécuter :
    php Init_elasticsearch_index.php 
    Ce fichier permet de définir le template que doit utiliser elasticsearch.
    
    Vous pouvez créer le fichier config manuellement ou utiliser le script configure.sh qui permet de créer un fichier de config facilement et rapidement
    
    Rendez vous dans Frontend/config.ini
    REPOSITORY_NAME = Defini le nom du repository ainsi que le nom utilisé pour la generation des DOIs
    REPOSITORY_URL = Indiquer ici l'url sur lequel le projet sera hebergé
    UPLOAD_FOLDER = défini ou les Uploads des utilisateurs vont être stockés, 
    choisissez un chemin et vérifier les permissions.
    DATAFILE_UNIXUSER=Il s'agit du user a qui appartient les fichiers uploader ( niveau system de fichier).
    NO_REPLY_MAIL=Mail de No-reply
    SOCIAL_SHARING=Activer l'option de partage sur les reseaux sociaux
    SSH_HOST=Host ssh ou sont presente les données OTELO-CLOUD
    SSH_UNIXUSER=USER ssh
    SSH_UNIXPASSWD=password
    SMTP=votre smtp
    admin[]= l'adresse mail des administrateurs avec des doubles quotes séparé d'une virgule
    #ELASTICSEARCH CONFIG
    ESHOST=Host d'elasticsearch
    ESPORT=Port d'elasticsearch
    #Choisissez l’authentification de mongodb 
    host = 127.0.0.1
    port = 27017
    authSource= Le nom de votre BDD qui contiendra les jeux de données
    username = Le username de votre BDD qui contiendra les jeux de données
    password = Le mot de passe de votre BDD qui contiendra les jeux de données
    #DOI BDD
    DOI_PREFIX = Votre prefix DOI datacite
    DOI_database = Le nom de votre BDD qui contiendra le numéro de DOI
    user_doi = Le username de votre BDD qui contiendra le numéro de DOI
    password_doi = Le mot de passe de votre BDD qui contiendra le numéro de DOI
    Auth_config_datacite = Token d'authentification (Basic https) de datacite
     



**Parametrage du fichier de configuration de mongo_connector:**

Il s'agit du user qui a les droits de backup.
Définissez un username, ainsi qu'un password.


**Initialisation du mapping d'elasticsearch:**

Afin d’initialiser le mapping, qui va permettre un bon fonctionnement des facettes de recherche, il faut lancer le script Init_elasticsearch_index.php.
il doit vous retourner acknowledge:true.


**Lancez Mongo-connector**

    sudo mongo-connector -m localhost:27017 -c mongo-connector_config.json  --namespace NOMDELABDD.*

    Mongo connector permet de répliquer les données présentes dans mongoDB sur un cluster elasticsearch.

    Ci dessous un schéma explicatif de son fonctionnement :

![Alt text](/Img_doc/Mongoconnector.png?raw=true)




# Organisation  <a name="organisation"></a>

**Organisation des bases de données:**

Ordar comporte 2 bases de données:

    - ORDAR
    - DOI
    
 La base Ordar contient plusieurs collections, une depot manuel : Manual_Depot et plusieurs autres en fonction des projets importés avec OteloCloud.
 
 La base DOI contient une seule collection, DOI, elle contient un document avec un ID ORDAR-DOI, un ID est l’état du document (cet état permet de gérer des accès concurrents, locked/unlocked est positionné pour utiliser la ressource). 


**Organisation du code:**

    --src
        --search
            --controller contient les controllers ainsi qu'un fichier de configuration 
            --templates contient les templates html
        --index.php : fichier de routes
        --img contient les images 
        --js contient les scripts js ainsi que des librairies
        --css contient les feuilles de styles
    --vendor : contient les dépendances slim nécessaires au routage


**Détails des différentes classes et fonctions PHP:**

-**RequestController**: 

Cette classe va permettre d'effectuer les requêtes vers l'api ElasticSearch et récupérer les données pour les envoyer vers datacite puis envoi un mail à un administrateur.


-**DatasheetController**: 

Cette classe permet de gérer les datasets , créer , éditer, supprimer, générer un doi, envoyer un mail à un auteur.
Avant un ajout d'un jeu de données ou modification d'un jeu existant, une vérification de disponibilité du service Datacite est effectué.



-**FileController**: 

Cette classe permet d'effectuer des actions de téléchargement, de preview en ligne de certains fichiers (extension disponible en preview : txt, png, jpg, gif, pdf.
Elle permet aussi un export des métadonnées en différents format: Datacite, Dublincore, JSON , BibTex.



**Détails des différentes module JS:**
    
   L'application est composée de six modules JS différents:
        
        - datatable (Affichage des résultats sous forme de pagination)
        - search (permet de rechercher un terme , par facets ou non)
        - mypublications (Affichage des publications de l'utilisateur courant, permet aussi une recherche par facettes)
        - upload (permet de controller le formulaire upload et edit, rends dynamique le formulaire et réalise les check de contenu Frontend)
        - preview (Affichage d'un modal permettant de visualiser un fichier dans une iframe)
        - send_email (Affichage d'un modal permettant d'envoyer un message à un auteur (contact depuis un jeu de données) ou aux administrateurs (contact depuis le footer de l'application)




**Aspect de générale de l’application :**

Pour l’aspect, le framework Semantic UI a été choisi pour sa simplicité d’utilisation et sa bonne documentation. Il permet de réaliser des interfaces graphiques responsives légères et rapides.

![Alt text](/Img_doc/Ordar_accueil.png?raw=true)


# Utilisation  <a name="utilisation"></a>

**Définition d'un jeu de données:**

Un jeu de données est un ensemble constitué de métadonnées et de fichiers données.
Les métadonnées contiennent des champs obligatoires et facultatifs.
Un jeu de données à un accès défini: il peut être :

    - Open(libre de consultation).
    - Closed(Seulement les métadonnées sont accessibles).
    - Embargoed(métadonnées uniquement accessibles, les fichiers seront publié et disponible à une date fournie par le publiant).
    - Unpublished (fichier importé d'OTELoCloud (avec un script d'import) que le propriétaire peut publier quand il le souhaite suivant les trois modes précedent).


**Modification du statut Embargoed -> Open:**
Le script Check_Embargoed_access.php doit être exécuté une fois par jour afin de changer les statuts des jeux de données arrivé à échéance: des lors que la date du jour est égale à la date de l'embargo, le jeu de données est modifié en statut Open.


**Rechercher un jeu de données:**
L'utilisateur peut effectuer une recherche par mot clé, il peut utiliser des opérateurs logique tels que AND et OR.
Une recherche peut etre effectue uniquement sur un champs spécifique, voici une liste des champs interogeable et comment effectué cette recherche:
    - TITLE : INTRO.TITLE:"mot clé à chercher"
    - DESCRIPTION : INTRO.DATA_DESCRIPTION:"mot clé à chercher"
    - AUTEURS : INTRO.FILE_CREATOR.DISPLAY_NAME:"mot clé à chercher"
    - LANGAGE : INTRO.LANGUAGE:"mot clé à chercher"
    
A l'issue de cette recherche l'utilisateur peut trier les données à l'aide de facettes:
   
    -Sample kind
    -Authors
    -Keywords
    -Scientific fields
    -Languages
    -Filetypes
    -Access right
    -Date
    
Seulles les facettes Access right ont l’opérateur OR

Lors de la sélection de plusieurs facettes, l'opérateur de recherche est AND. 


**Configuration de l'authentification:**

L'authentification s'effectue via la route /login.
Cette route recupère les variables contenu dans les headers HTTP d'un serveur d'authentification ( dans notre exemple shibboleth) et va assigné les variables de session php avec leurs valeurs.
Variables utilisés:

	-HTTP_SN
	-HTTP_GIVENNAME
	-HTTP_MAIL
	
Voici un schema explicatif du fonctionnement:

![Alt text](/Img_doc/config_login.png?raw=true)


Voici un exemple de code pour configurer apache avec shibboleth:

	<Location />
	     AuthType shibboleth
	     Require shibboleth
		ShibRequestSetting applicationId ordar
	   </Location>

	<Location /login>
		# Auth Shibb
		AuthType shibboleth
		ShibRequestSetting requireSession true
		ShibRequestSetting applicationId ordar

		ShibUseHeaders On
		ShibRequireSession On
	       	AuthGroupFile /etc/ordar.conf #Ajout du fichier contenant les utilisateurs autorisés
		Require group ordar

	</Location>


**Insertion d'un nouveau jeu de données:**

Avant tout dépot de jeu de données une vérification de disponibilité de l'API datacite est effectuée.

L'utilisateur rempli le formulaire,il rempli les champs marqués d'une étoile rouge qui sont obligatoires, la vérification est faite coté client et coté serveur.

Un numéro de DOI pérenne sera attribué au jeu de données.

Les informations sont ensuite traitées et insérées en base de données.

Mongo connector se charge ensuite d'indexer ces données.

L'utilisateur recevra un mail avec le DOI qui a été attribué au jeu de données.

**Importation d'un jeu de données via OTELoCloud:**

OTELO utilise des canevas afin que les chercheurs puissent créés des fichiers de données interroperable.

Un script d'importation se charge d'importer les données présente dans un dossier spécifiques d'OTELoCloud:
Les métadonnées issues du feuillet INTRO du fichier excel sont importées dans la base Mongodb,
Le feuillet DATA est converti en fichier csv et il est joint à ce jeux de données comme fichier.

Vous trouverer le script d'importation ici : https://bitbucket.org/arnouldpy/ordar_scripts/
Le repository est privée, il sera necessaire de nous envoyer un message afin de répondre à votre demande.

Le jeux de données est ajouté avec un statut Unpublished, il est accessible uniquement au propriétaire ou ses co-auteurs.
L'auteur peut choisir de le publier ou de le supprimer.
Si le jeu de données est supprimé ou publié, le fichier xlsx est automatiquement supprimé du repertoire source.

L'utilisateur recevra un mail avec le DOI qui a été attribué dés la publication du jeu de données. En statut unpublished l'id du fichier est provisoire (aucun DOI n'est attribué).

**Création d'un fichier brouillon dit "Draft":**

L'utilisateur peut créer un fichier brouillons , il doit renseigner au minimum le titre ainsi que le langage.
Une fois créer le fichier apparait dans la section "my dataset" uniquement pour l'utilisateur qui a créé ce jeu de données, il peut l'editer puis le publier ou le supprimer à tout moment.

**Modification d'un jeu de données existant:**

L'utilisateur se rend sur la donnée à modifier, il clique sur edit, un formulaire apparaît avec les métadonnées présentes en base de données, l'utilisateur peut les modifier, il ne peut pas modifier ou ajouter des fichiers.


**Suppression d'un jeu de données:**

Un jeu de données peut être supprimé si il a un statut unpublished, c'est à dire sans DOI.
La suppression entraîne la suppression TOTALE du jeu de données:

-Le fichier original (dans OTELoCloud)

-Le csv généré

-l'entrée en base de données



![Alt text](/Img_doc/Diagram_ORDAR.png?raw=true)



**Enregistrement des DOIs:**

![Alt text](/Img_doc/DOI_save.png?raw=true)




**Mode Administrateur:**

Il existe un mode administrateur qui permet de visualiser tous les jeux de données, même les données "Unpublished".
L'administrateur peut visualiser, modifier, et supprimer un jeu de données, même si celui ci a été publié.
Dans le cas ou l'administrateur supprime un jeu de données le DOI est alors désactivé.

L'administrateur peut aussi modifier et ajouter des fichiers (avec un minimum de 1 fichier par jeux de données)

 **Détails des clés mongo:**
 
    _id: DOI
    INTRO:
            TITLE: Titre
            LANGUAGE: Langage
            FILE_CREATOR:   
                        FIRST_NAME: Prénom
                        NAME: Nom
                        DISPLAY_NAME: Prénom et nom
                        MAIL: Mail du créateur
            DATA_DESCRIPTION: Description des données
            PUBLISHER: Editeur
            SCIENTIFIC_FIELD: Champs scientifiques
            INSTITUTION: Institutions
            METHODOLOGY:
                        NAME:Nom
                        DESCRIPTION:Description
            MEASUREMENT:
                        NATURE
                        ABBREVIATION
                        UNIT
           LICENSE:Licence
           ACCESS_RIGHT:Droits d'accés
           METADATA_DATE:Date de dernieres modifications des metadonnées
           CREATION_DATE:Date de création initiale du jeu de données 
           UPLOAD_DATE:Date d'ajout dans l'entrepot
           PUBLICATION_DATE:Date dde publication des données
    DATA:
          FILES:
                DATA_URL:Denomination du fichier
                FILETYPE:Extension du fichier

# Docker  <a name="docker"></a>

**Docker:** 


Pour utiliser Docker vous devez configurer le fichier Configure.env qui contient toutes les variable de configuration des différents services:
Voici uen configuration de test, à vous de la modifier.

	#SPECIFIC ORDARUI
	REPOSITORY_NAME=DOCKER-ORDAR
	REPOSITORY_URL=http://example.fr
	UPLOAD_FOLDER=/data/applis/ORDaR/Uploads/ NE PAS MODIFIER LE CHEMIN, il s'agit du chemin INTERNE du docker

	ADMIN=admin@admin.fr
	DATAFILE_UNIXUSER="owncloud"
	NO_REPLY_MAIL="Noreply@ordar.fr"
	SOCIAL_SHARING=true

	#DOI CONFIG
	DOI_PREFIX=10.5072
	DOI_database=DOI
	user_doi=test4
	password_doi=test4
	SSH_HOST=IPofservice
	SSH_UNIXUSER=user
	SSH_UNIXPASSWD=pass
	SMTP=votre SMTP
	#DATACITE CREDENTIALS

	AUTH_CONFIG_DATACITE="YOUR SECRETS CREDENTIALS HERE"

	#MONGO CONFIG
	HOST_MONGO=mongo
	PORT_MONGO=27017
	BDDNAME=ORDaR
	SUPER_USER_NAME=test
	SUPER_USER_PASSWORD=test
	USER_READWRITE=test2
	PASSWORD_READWRITE=test2
	USER_BACKUP=test3
	PASSWORD_BACKUP=test3


	#ELASTICSEARCH
	ESHOST=elasticsearch
	ESPORT=9200


	#SPECIFIC OAI PMH
	REPOSITORY_URL_OAI=test
	PROTOCOL_VERSION=3.0
	ADMINMAIL=test@test.fr
	GRANULARITY=YYYY-MM-DD
	TOKENKEY="test"
	SpecialSet="openaire"


Note: Les utilisateurs mongo sont créés automatiquement.

Le service Ordar_script permet de mettre en place l'upload automatic des jeux de données d'un projet,
pour cela configurer le fichier Docker/Ordar_script/config.ini avec les valeurs prédemment rentré.

ATTENTION: Un projet = un service d'upload automatique!

ATTENTION: Dans les services OrdarUI et  Ordar_scripts_mobised ( ou pour tout autre projet), il faut configurer les volumes afin de monter les fichiers Uploader et les jeux de donné présent sur OTELO-CLOUD.
Pour cela rendez-vous dans le fichier docker-compose.yml :

Exemple pour le service Ordar_script_mobised

	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)
     - /data/applis/ORDaR/excel/:/data/applis/ORDaR/excel/ (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)

Exemple pour le service OrdarUI
	 
	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)


Modifier le fichier Docker/Apache_PHP/ssmtp.conf:

	mailhub= ADRESSE DE VOTRE SMTP

Modifier le fichier Docker/Ordar_script/Dockerfile:

Ajouter votre access token bitbucket afin de pouvoir cloner le projet ordar_script

	pour créer votre access token (valable 1 heure), se rendre sur le compte bitbucket :settings : OAuth
	copier votre "key" et votre "secret"
	
	-> générer votre token : 
	curl https://bitbucket.org/site/oauth2/access_token -d grant_type=client_credentials -u key:secret

Un fois cela effectué, lancé docker-compose:

	docker-compose up

Patientez  pendant les installations et les initialisations.

Executez cette commande afin d'initialiser le mapping ElasticSearch: 

	docker exec ordar_ui php ORDaR/Init_elasticsearch_index.php


L'installation est terminé!


Pour stopper les containers:

	docker-compose stop


Pour les lancer :

	docker-compose start



Il y a 2 volumes présent sur ce projet afin de garantir la persistance des données:

	- mongodb, pour la base de données
	- elasticsearch, pour les données indexé par ES

Les fichiers de données sont stocké sur le systeme hôte et ensuite monté dans les différents container qui les utilisent.



ATTENTION: Mongo-connector indexe 2 minutes apres le lancement des conteneurs, ceci est du au demmarrage des différents services (mongo, elasticsearch)


















