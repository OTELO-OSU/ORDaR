![Alt text](/Frontend/src/img/logo.png?raw=true)

# ORDaR

Sommaire:
=================
* [Présentation](#presentation)
* [Installation](#installation)
* [Organisation](#organisation)
* [Utilisation](#utilisation)
* [Docker](#docker)


# Présentation <a name="presentation"></a>
ORDaR est un entrepot de données developpé par et pour OTELO, celui-ci est entierment généralisable.
Vous pouvez choisir le nom de l'entrepot avec le fichier de config detailler dans l'installation.
Ainsi vous pouvez deployer facilement cette entrepot pour votre institution.

ORDaR offre un environnement sécurisé et pérenne pour le dépôt de jeux de données et permet d'obtenir un DOI (Digital Object Identifier) pour publication ou datapaper.

Les ensembles de données peuvent etre publiés dans ORDaR sous différents accées:

	 Ouvert: accès total (opendata)
	 Fermé: Accès restreint aux seules métadonnées
	 Embargo: accès restreint aux métadonnées seulement jusqu'à la date de publication fixée par le producteur du jeux de données
	 

Toutes les métadonnées sont ouvertes et décrites dans les normes internationales, elles sont téléchargeables en 4 formats:

	 Datacite
	 BibteX
	 JSON
	 DublinCore
	 
Enfin, chaque ensemble de données est publié avec une licence Creative Common qui spécifie les termes de réutilisation juridique

Cette entrepôt vise à promouvoir l'accès ouvert: valoriser et partager les connaissances.

# Installation  <a name="installation"></a>

* [Installation](/Docs/Installation.md)

# Organisation  <a name="organisation"></a>

* [Organisation](/Docs/Organisation.md)

# Utilisation  <a name="utilisation"></a>

* [Utilisation](/Docs/Utilisation.md)

# Docker  <a name="docker"></a>

Pour utiliser Docker vous devez configurer le fichier Configure.env qui contient toutes les variable de configuration des différents services:
Voici une configuration de test, à vous de la modifier.

	#SPECIFIC ORDARUI
	REPOSITORY_NAME=DOCKER-ORDAR
	REPOSITORY_URL=http://example.fr
	UPLOAD_FOLDER=/data/applis/ORDaR/Uploads/ NE PAS MODIFIER LE CHEMIN, il s'agit du chemin INTERNE du docker

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
	DATASET_FILES_MAX_SIZE= Taille des fichiers maximum par dataset  
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
	
	#MYSQL AUTH DB
	DRIVER=mysql
	HOSTMYSQL=mysql_db
	MYSQL_ROOT_PASSWORD=root
	MYSQL_DATABASE=authentication
	MYSQL_USER=test2
	MYSQL_PASSWORD=test
	CHARSETMYSQL=utf8
	COLLATIONMYSQL=utf8_unicode_ci


Note: Les utilisateurs mongo et mysql sont créés automatiquement.

Le service  harvester-geo-stations permet de mettre en place l'upload automatic des jeux de données d'un projet,
pour cela configurer le fichier Docker/harvester-geo-stations/config.ini avec les valeurs prédemment rentré.

ATTENTION: Un projet = un service d'upload automatique!

ATTENTION: Dans les services OrdarUI et  harvester-geo-stations, il faut configurer les volumes afin de monter les fichiers Uploader et les jeux de donné présent sur OTELO-CLOUD.
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

Modifier le fichier Dockerfile a la racine du projet:
Remplacer TAILLESOUHAITE par une taille

	RUN echo 'upload_max_filesize = TAILLESOUHAITE' >> /usr/local/etc/php/php.ini
	RUN echo 'post_max_size = TAILLESOUHAITE' >> /usr/local/etc/php/php.ini


Modifier le fichier Docker/harvester-geo-stations/Dockerfile:

Ajouter votre access token bitbucket afin de pouvoir cloner le projet ordar_script

	pour créer votre access token (valable 1 heure), se rendre sur le compte bitbucket :settings : OAuth
	copier votre "key" et votre "secret"
	
	-> générer votre token : 
	curl https://bitbucket.org/site/oauth2/access_token -d grant_type=client_credentials -u key:secret

Un fois cela effectué, lancé docker-compose:

	docker-compose up

Patientez  pendant les installations et les initialisations.

L'installation est terminé!

Pour stopper les containers:

	docker-compose stop


Pour les lancer :

	docker-compose start


Il y a 3 volumes présent sur ce projet afin de garantir la persistance des données:

	- mongodb, pour la base de données
	- elasticsearch, pour les données indexé par ES
	- mysql , pour les comptes utilisateurs

Une fois déployer, vous pouvez vous loguer avec un compte ADMIN générique afin de créer le votre, et ainsi supprimer celui ci.

	Adresse mail : admin@admin.fr
	Mot de passe: admin@ORDAR1

Les fichiers de données sont stocké sur le systeme hôte et ensuite monté dans les différents container qui les utilisent.


ATTENTION: Mongo-connector indexe 2 minutes apres le lancement des conteneurs, ceci est du au demmarrage des différents services (mongo, elasticsearch)
