# Installation avec Docker  <a name="docker"></a>

Nous fournissons dans le dépot, les fichiers de configuration nécessaires au déploiement docker d'une instance ORDaR.

### Première étape : installer docker sur votre machine :
	https://docs.docker.com/engine/installation/

### Deuxieme étape : cloner le depot sur votre système avec git

		git clone https://github.com/OTELO-OSU/ORDaR.git
		
### Troisème étape : Adapter les fichiers de configuration :

#### Fichier Configure.env		
Vous devez configurer le fichier Configure.env qui contient toutes les variable de configuration des différents services docker (sauf docker apache) :

Voici une configuration de test, à vous de l'adapter.

		############ SPECIFIQUE ORDARUI ##############
		
		#Defini le nom du repository ainsi que le nom utilisé pour la generation des DOIs 
		REPOSITORY_NAME=DOCKER-ORDAR
		
		#url du projet
		REPOSITORY_URL=https://test-ordar.univ-lorraine.fr
		
		#défini l'emplacement des Uploads des utilisateurs (A ne pas modifier)
		UPLOAD_FOLDER=/data/applis/ORDaR/Uploads/
		
		#user à qui appartient les fichiers uploader (niveau système de fichier).
		DATAFILE_UNIXUSER="toto"
		
		NO_REPLY_MAIL="Noreply@ordar.fr"
		
		#Activation/désactivation du partage via les réseaux sociaux	
		SOCIAL_SHARING=true
		
		############ SPECIFIQUE AU SCRIPT DE MOISSONNAGE (ne pas implémenter) ##################
		#SSH_HOST=IPofservice
		#SSH_UNIXUSER=user
		#SSH_UNIXPASSWD=pass
		##############################
		
		#L'adresse de votre relais de messagerie
		SMTP="smtp-int.univ-lorraine.fr"
		
		# Valeur maximale pour 1 jeux de données (pouvant être constitué de plusieurs fichiers)
		DATASET_FILES_MAX_SIZE=1G
	
		#DOI CONFIG
		DOI_PREFIX=XX.XXXX
		DOI_database=DOI
		user_doi=test4
		password_doi=test4

	
		#DATACITE CREDENTIALS
		AUTH_CONFIG_DATACITE="YOUR SECRETS CREDENTIALS HERE"
	
	#############	
	# Note : La clef permettant à l'application de se connecter à DataCite pour l'enregistrement des DOI
	# est obtenu grace à la commande : "echo login_datacite:mdp_datacite | base64"
	#############
	
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
		# clef de chiffrement
		TOKENKEY="test"
		SpecialSet="openaire"

	#############
	# Note: Pour plus de détails sur le fonctionnement de cette partie : 
	# https://github.com/OTELO-OSU/ORDaR_OAI-PMH 
	#############

		#MYSQL AUTH DB
		DRIVER=mysql
		HOSTMYSQL=mysql_db
		MYSQL_ROOT_PASSWORD=root
		MYSQL_DATABASE=authentication
		MYSQL_USER=test2
		MYSQL_PASSWORD=test
		CHARSETMYSQL=utf8
		COLLATIONMYSQL=utf8_unicode_ci	
	
	#############
	# Note 1 : pour plus d'information sur cette partie se reporter à la section 
	# "configuration de l'authentification" dans Installation (hors docker)
	# Note 2: Les utilisateurs mongo et mysql sont créés automatiquement.
	#############
	
		#ORCID
		ORCID_client_id="Your key"
		ORCID_client_secret="Your secret"
		
	###########
	# Pour utiliser ORCID, vous devez aussi modifier la valuer clientid (ligne 1546 et 1627) 
	# dans le fichier Frontend/src/js/search.js.
	#Pour générer vos clientid et secret aller dans "developer tool" de votre profil orcid
	# -> declarer les url suivantes : https://votre_entrepot.fr/signup et
	# https://votre_entrepot.fr/myaccount
	###################
	
#### Fichier ssmtp.conf pour apache
Configuration du relais mail :
Modifier le fichier Docker/Apache_PHP/ssmtp.conf:

	mailhub= ADRESSE DE VOTRE SMTP

#### Parametrage de votre php max file size (serveur Hôte)
Paramétrage de la taille de fichier maximale au niveau php (Host) :
Modifier le fichier Dockerfile a la racine du projet:

Remplacer TAILLESOUHAITE par une taille

	RUN echo 'upload_max_filesize = TAILLESOUHAITE' >> /usr/local/etc/php/php.ini
	RUN echo 'post_max_size = TAILLESOUHAITE' >> /usr/local/etc/php/php.ini

### Quatrième étape : passage en revue du fichier docker-compose.yml (servant à générer les images)

voici les différents services qui seront créés pour l'installation :

- Mongo (base mongodb hebergeant les jeux de données)

- Mysql_db (base de données pour l'authentification utilisateurs)

- Elasticsearch (moteur de recherche et d'indexation)

- logstash (centralise et parse les logs d'accès)

- kibana (Exploration et visualisation des logs d'accès : accès par défaut restreint en local)

- mongoconnector (synchronise la base mongo avec le moteur d'indexation elasticsearch)

- OrdarUI (interface de l'application ORDaR)

- OrdarOAIPMH (permet de rendre l'entrepot moissonable selon le protocole OAIPMH : activé par défaiut)

- CheckEmbargoedDate (script de verification des dates d'embargo (passage en open access) lancé tout les jour à 00h01)

- harvester-geo-stations (script de moissonage des espaces collaboratif : Spécifique OTELo)

Le service  harvester-geo-stations permet de mettre en place l'upload automatic des jeux de données d'un projet,
pour cela configurer le fichier Docker/harvester-geo-stations/config.ini avec les valeurs prédemment rentré.
Scripts privés disponible sur demande.
-> ATTENTION: Un projet = un service d'upload automatique!

Le script de moissonage harvester-geo-stations étant stocké sur un repository privé de bitbucket :
Modifier le fichier Docker/harvester-geo-stations/Dockerfile:

Ajouter votre access token bitbucket afin de pouvoir cloner le projet ordar_script

	pour créer votre access token (valable 1 heure), se rendre sur le compte bitbucket :settings : OAuth
	copier votre "key" et votre "secret"
	
	-> générer votre token : 
	curl https://bitbucket.org/site/oauth2/access_token -d grant_type=client_credentials -u key:secret

#### ATTENTION: Dans les services OrdarUI et  harvester-geo-stations, il faut configurer les volumes afin de monter les fichiers déposé et les jeux de donné issu de l'espace collaboratif (si activation du service)
Pour cela rendez-vous dans le fichier docker-compose.yml :

Exemple pour le service harvester-geo-stations

	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)
     - /data/applis/ORDaR/excel/:/data/applis/ORDaR/excel/ (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)

Exemple pour le service OrdarUI
	 
	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)



### Cinquième et dernière étape :

Lancer docker-compose:

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
