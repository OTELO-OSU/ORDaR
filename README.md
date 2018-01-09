![Alt text](/Frontend/src/img/logo.png?raw=true)

# ORDaR

Sommaire:
=================
* [Présentation](#presentation)
* [Fonctionalités](#Fonctionalités)
* [Installation (non docker)](/Docs/Installation.md)
* [Organisation (code et Bdd)](/Docs/Organisation.md)
* [Utilisation](/Docs/Utilisation.md)
* [Docker](#docker)


# Présentation <a name="presentation"></a>
ORDaR est un entrepôt de données développé par et pour OTELO, celui-ci est entièrement personnalisable.
A l'installation, vous pouvez choisir le nom de votre entrepot grâce au fichier de configuration detaillé dans la rubrique : [Installation](/Docs/Installation.md) mais nous vous invitons à choisir la solution de déploiement via [Docker](#docker).
Vous pouvez ainsi déployer facilement une instance de cette entrepot pour votre institution.

ORDaR offre un environnement sécurisé et pérenne (sous réserve du contrat de service de votre hebergeur et des procédures de sauvegarde mise en place) pour le dépôt de jeux de données et permet d'obtenir un DOI (Digital Object Identifier) pour vos publication ou datapaper.


	Cette entrepôt vise à promouvoir l'accès ouvert: valoriser et partager les connaissances.


**Aspect général de l’application :**

![Alt text](/Img_doc/Ordar_accueil.png?raw=true)


# Fonctionalités <a name="fonctionalite"></a>

- Deux profils utilisateurs :
	* Admin (droit de lecture et ecriture sur tous les jeux de données)
	* User (droit de lecture et ecriture sur les jeux de données déposés par le User)

- Possiblité de lier son compte avec son Orcid id

- Dépots de jeux de données suivant différents niveaux de droit d'accès:

	 Ouvert: accès total (opendata)
	 Fermé: Accès restreint aux seules métadonnées
	 Embargo: accès restreint aux métadonnées seulement jusqu'à la date de publication fixée par le producteur du jeux de données
	 
	 
- Attribution d'une licence Creative Common v4 sur les jeux de données publié.

- Attribution d'un DOI (Digital Object Identifier) pour vos jeux de données publié.

- Signalement des jeux de données via les réseaux sociaux : facebook / linkedIn / tweeter

- Mise à jour possible des métadonnées de vos jeux de données (avec suivi des modifications)

- Accessiblité des métadonnées dans les normes internationales, téléchargeables en 4 formats:

	 Datacite
	 BibteX
	 JSON
	 DublinCore
	 
- Prévisualisation des jeux de données (pour les formats ascii, pdf, jpg, png)

- Visualisation cartographique (dans le cas données geospatialisées)

- Suivi du nombre de téléchargement des jeux de données.

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
	TOKENKEY="test" #Clé a utiliser pour chiffrer les resumptionTokens
	SpecialSet="openaire" #Set qui sera appliqué a tout les documents pour permettre d'etre recupérer par openaire ou autre. les valeurs doivent etre séparé par une virgule.

	
	#MYSQL AUTH DB
	DRIVER=mysql
	HOSTMYSQL=mysql_db
	MYSQL_ROOT_PASSWORD=root
	MYSQL_DATABASE=authentication
	MYSQL_USER=test2
	MYSQL_PASSWORD=test
	CHARSETMYSQL=utf8
	COLLATIONMYSQL=utf8_unicode_ci
	
	#ORCID
	ORCID_client_id="Your key"
	ORCID_client_secret="Your secret"


Note: Les utilisateurs mongo et mysql sont créés automatiquement.

Le service  harvester-geo-stations (spécifique à OTELo) permet de mettre en place l'upload automatic des jeux de données d'un projet,
pour cela configurer le fichier Docker/harvester-geo-stations/config.ini avec les valeurs prédemment rentré.
Scripts privés disponible sur demande.

Pour utiliser ORCID, vous devez configurer lla variable client ID et secret dans le fichier config, vous devez aussi modifier la valuer clientid (ligne 1546 et 1627) dans le fichier search.js.

ATTENTION: Un projet = un service d'upload automatique!

ATTENTION: Dans les services OrdarUI et  harvester-geo-stations, il faut configurer les volumes afin de monter les fichiers Uploader et les jeux de donné présent sur OTELO-CLOUD.
Pour cela rendez-vous dans le fichier docker-compose.yml :

Exemple pour le service harvester-geo-stations

	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)
     - /data/applis/ORDaR/excel/:/data/applis/ORDaR/excel/ (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)

Exemple pour le service OrdarUI
	 
	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)

Configuration du relais mail :
Modifier le fichier Docker/Apache_PHP/ssmtp.conf:

	mailhub= ADRESSE DE VOTRE SMTP

Paramétrage de la taille de fichier maximale au niveau php :
Modifier le fichier Dockerfile a la racine du projet:
Remplacer TAILLESOUHAITE par une taille

	RUN echo 'upload_max_filesize = TAILLESOUHAITE' >> /usr/local/etc/php/php.ini
	RUN echo 'post_max_size = TAILLESOUHAITE' >> /usr/local/etc/php/php.ini


Le script de moissonage harvester-geo-stations étant stocké sur un repository privé de bitbucket :
Modifier le fichier Docker/harvester-geo-stations/Dockerfile:

Ajouter votre access token bitbucket afin de pouvoir cloner le projet ordar_script

	pour créer votre access token (valable 1 heure), se rendre sur le compte bitbucket :settings : OAuth
	copier votre "key" et votre "secret"
	
	-> générer votre token : 
	curl https://bitbucket.org/site/oauth2/access_token -d grant_type=client_credentials -u key:secret

Un fois cela effectué, lancé docker-compose:

	docker-compose up

Patientez  pendant les installations et les initialisations.

L'installation est terminée!

Pour stopper les containers:

	docker-compose stop


Pour les lancer :

	docker-compose start


Il y a 3 volumes présent sur ce projet afin de garantir la persistance des données:

	- mongodb, pour la base de données
	- elasticsearch, pour les données indexées par ES
	- mysql , pour les comptes utilisateurs

Une fois déployé, vous pouvez vous loguer avec un compte ADMIN générique afin de créer le votre, et par la suite supprimer celui ci.

	Adresse mail : admin@admin.fr
	Mot de passe: admin@ORDAR1

Les fichiers de données sont stockés sur le systeme hôte et ensuite monté dans les différents container qui les utilisent.

Le container OAI-PMH est activé par défaut, ce qui rends l'entrepôt de données moissonable, libre à vous de ne pas le demarrer si vous ne souhaitez pas mettre à disposition le protocole OAI-PMH.

ATTENTION: Mongo-connector indexe 2 minutes après le lancement des conteneurs, ceci est du au demmarrage des différents services (mongo, elasticsearch)
