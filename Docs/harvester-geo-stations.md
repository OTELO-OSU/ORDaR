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
	
	#### ATTENTION: Dans le services harvester-geo-stations, il faut configurer les volumes afin de mapper le repertoire interne docker avec le file system	hôte des jeux de donnés deposé (espace collaboratif)
	
Pour cela rendez-vous dans le fichier docker-compose.yml de la racine du projet :

Exemple pour le service harvester-geo-stations

	 volumes:
	     - /data/applis/ORDaR/Uploads/:/data/applis/ORDaR/Uploads/  (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)
     - /data/applis/ORDaR/excel/:/data/applis/ORDaR/excel/ (Chemin machine hôte : Chemin du docker interne NE PAS MOFIFIER LE CHEMIN INTERNE)
