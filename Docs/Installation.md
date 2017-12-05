# Installation  

**Prérequis :**

    -MongoDB 3.4.2

    -Elasticsearch 5.2

    -Mongo connector 

    -PHP 5.6

    -PHP-curl
    
    -PHP libssh2

    -Mongo php driver
    
    -MYSQL


Pour ubuntu 16.04(pour d’autre systèmes consulter le manuel de mongodb)

**Installation de mongodb :**

    sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
    echo "deb [ arch=amd64,arm64 ] http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
    sudo apt-get update
    sudo apt-get install -y mongodb-org
    
    
**Installation de mysql:**

    apt-get install mysql-server mysql-client libmysqlclient15-dev mysql-common



**Installation d’elasticsearch**

    Oracle JDK doit être installé avant de continuer.

    curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.2.2.tar.gz

    tar -xvf elasticsearch-5.2.2.tar.gz

    cd elasticsearch-5.2.2/bin


**Installation de mongo-connector**

    apt-get install python-pip
    pip install 'mongo-connector[elastic5]'

**Installation php :**

    sudo apt-get install  php5.6

    Installer php curl :

    sudo apt-get install php5.6-curl
    
    et php-mongo et phplibssh2

    sudo apt-get install php-mongo

    sudo apt-get install libssh2-1-dev 
   
    pecl install ssh2

    On active les extensions en ajoutant les lignes suivantes au php.ini

	extension=mongo.so
	extension=ssh2.so

Afin d'envoyer des mails, vous devez configurer un SMTP sur votre serveur.


**Récupérer le projet :**

    git clone https://github.com/arnouldpy/ORDaR.git

    Rendez vous dans le dossier créé, une fois dans le dossier Ordar, exécuter :
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
    DATASET_FILES_MAX_SIZE= Nombre d'espace à allouer (ex: 1G pour 1 gigas)
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
    

**Configuration apache2**

    activer mode rewrite :
    sudo a2enmod rewrite

    Modifier la configuration apache:
    DocumentRoot /var/www/html/ORDaR/Frontend/src/


    <Directory "/var/www/html/ORDaR/Frontend/src/">
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>

**Configuration php.ini:**

	upload_max_filesize = 1G

	post_max_size = 1050M


	
**Demarrer la base mongo en mode replica set :**
    
    sudo mongod --replSet "rs0"

    Démarrer shell mongo et exécuter :
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
rendez vous dans le dossier précédemment téléchargé /bin et exécuter :
./elasticsearch


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

**Configuration de l'authentification:**


Demarrer le serveur mysql 


Executez cette commande (requiert les droits admin):
	
	mysql -h HOST-u USER -p PASSWORD < authentication.sql

Créé un utilisateur avec des droit limité à la base authentication (requiert les droits admin)

	CREATE USER 'USER'@'localhost' IDENTIFIED BY "PASSWORD";GRANT SELECT, INSERT, UPDATE, DELETE, FILE ON *.* TO 'USER'@'localhost';GRANT ALL PRIVILEGES ON `authentication`.* TO 'USER'@'localhost';

Une fois ceci fait,Editer le fichier Frontend/AuthDB.ini avec l'utilisateur précédemment crée:

	driver = mysql
	host = VOTRE_HOST
	database = authentication
	username = VOTRE_UTILISATEUR_LIMITE
	password = VOTRE_MOT_DE_PASSE_LIMITE
	charset = utf8
	collation = utf8_unicode_ci

	
La base est maintenant installé.

L'authentification s'effectue via la route /login.
On peut s'authentifier au compte utilisateur via l'authentification de l'application, necessitant une inscription de la part de l'utilisateur ou via le CAS de son etablissement. Pour cela l'administrateur devra importer les comptes utilisateurs a utiliser via le CAS ou l'utilisateur devra préalablement s'inscrire .

L'authentification vers le CAS s'effectue via la route /loginCAS.
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

	<Location /loginCAS>
		# Auth Shibb
		AuthType shibboleth
		ShibRequestSetting requireSession true
		ShibRequestSetting applicationId ordar

		ShibUseHeaders On
		ShibRequireSession On
	       	AuthGroupFile /etc/ordar.conf #Ajout du fichier contenant les utilisateurs autorisés
		Require group ordar

	</Location>
	
Il faut ensuite modifier la route logout afin de se deconnecter du serveur single sign in (Shibboleth, CAS).
Modifier le Redirect vers la route logout de votre service.



