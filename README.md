

# Installation ORDaR Beta 1



**Prérequis :**

    -MongoDB 3.4.2

    -Elasticsearch 5.2

    -Mongo connector 

    -PHP 5.6

    -PHP-curl

    -Mongo php driver


Pour ubuntu 16.04(pour d’autre système consulter le manuel de mongodb)

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
    
    et php-mongo

    sudo apt-get install php-mongo

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

    Démarré shell mongo et exécuter :
        rs.initiate()

    Se connecter sur la base admin:

        use admin

    Créé un utilisateur avec un role backup:

    db.createUser({user: "USER",pwd: "PASSWORD",roles: [ { role: "backup", db: "admin" } ]})

    Ensuite se connecter sur la base ORDaR et crée l'utilisateur qui pourra modifier les données:

        use ORDaR

        db.createUser({user: "USER",pwd: "PASSWORD",roles: [ { role: "readWrite", db: "ORDaR" } ]})

Ensuite démarrer elasticsearch,
rendez vous dans le dossier précédemment télécharger , dans le dossier bin et exécuter :
./elasticsearch

**Récupérer le projet :**

    git clone https://github.com/arnouldpy/ORDaR.git

    Rendez vous dans le dossier créer, une fois dans le dossier Ordar, exécuter :
    php Init_elasticsearch_index.php 
    Ce fichier permet de définir la template que doit utiliser elasticsearch.
    Rendez vous dans Frontend/src/search/controller/config.ini
    UPLOAD_FOLDER défini ou les Uploads des utilisateurs vont être stocké, 
    choisissez un chemin et vérifier les permissions.
    Il s'agit de l'user qui a les droits de d'écriture.
    Choisissez l’authentification de mongodb 
    host = 127.0.0.1
    port = 27017
    username =
    password =



** Parametrage du fichier de configuration de mongo_connector:**

Il s'agit de l'user qui a les droits de backup.
Definissez un username, ainsi qu'un password.




**Lancez Mongo-connector**

    sudo mongo-connector -m localhost:27017 -c mongo-connector_config.json  --namespace NOMDELABDD.*





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

Cette classe va permettre d'effectuer les requêtes,vers l'api ElasticSearch et récupérer les données nécessaires,vers datacite et l'envoie de mail à un administrateur.


-**DatasheetController**: 

Cette classe permet de gerer les datasets , en creer , editer, supprimer, generer un doi, envoyer un mail a un auteur.


-**FileController**: 

Cette classe permet d'effectuer des actions de telechargement, d'export et de preview de fichiers. 








# Utilisation

**Insertion d'un nouveau jeu de données:**

L'utilisateur rempli le formulaire,il rempli les champs marqué d'une étoile rouge qui sont obligatoire, la verification est faite coté client et coté serveur.

Un numero de DOI sera attribe au jeu de données.

Les informations sont ensuite traité et inserer en base de données.

Mongo connector se charge ensuite d'indexer ces données.


**Modification d'un jeu de données existant:**

L'utilisateur se rend sur la données a modifier, il clique sur edit, un formulaire apparait avec les metadonnées deja en base de données, l'utilisateur peut les modifier, il ne peut pas modifier ou ajouter des fichiers.


**Suppresion d'un jeu de données: **

Un jeu de données peut etre supprimé si il a un statut unpublished, c'est a dire pas de DOI.




 














