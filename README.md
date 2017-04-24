

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

    Créer aussi une base DOI et crée l'utilisateur qui pourra modifier les données:

        use DOI

        db.createUser({user: "USER",pwd: "PASSWORD",roles: [ { role: "readWrite", db: "DOI" } ]})

Ensuite démarrer elasticsearch,
rendez vous dans le dossier précédemment télécharger , dans le dossier bin et exécuter :
./elasticsearch

**Récupérer le projet :**

    git clone https://github.com/arnouldpy/ORDaR.git

    Rendez vous dans le dossier créer, une fois dans le dossier Ordar, exécuter :
    php Init_elasticsearch_index.php 
    Ce fichier permet de définir la template que doit utiliser elasticsearch.
    Rendez vous dans Frontend/config.ini
    UPLOAD_FOLDER défini ou les Uploads des utilisateurs vont être stocké, 
    choisissez un chemin et vérifier les permissions.
    Il s'agit de l'user qui a les droits de d'écriture.
    Choisissez l’authentification de mongodb 
    host = 127.0.0.1
    port = 27017
    authSource= Le nom de votre BDD qui contiendra les jeux de données
    username = Le username de votre BDD qui contiendra les jeux de données
    password = Le mot de passe de votre BDD qui contiendra les jeux de données
    DOI_PREFIX = Votre prefix DOI datacite
    URL_DOI = votre url d'enregitrement des DOIs
    DOI_database = Le nom de votre BDD qui contiendra le numero de DOI
    user_doi = Le username de votre BDD qui contiendra le numero de DOI
    password_doi = Le mot de passe de votre BDD qui contiendra le numero de DOI
    admin[]= l'adresse mail des administrateur entre double quotes séparé d'une virgule
    Auth_config_datacite = Token de d'authentification (Basic https) de datacite
     



**Parametrage du fichier de configuration de mongo_connector:**

Il s'agit de l'user qui a les droits de backup.
Definissez un username, ainsi qu'un password.


**Initialisation du mapping d'elasticsearch:**

Afin d'intialiser le mapping, qui va permettre un bon fonctionnement des facets de recherche, il faut lancer le script Init_elasticsearch_index.php.
Si tout s'est bien passé, il doit vous retourné acknowledge:true.


**Lancez Mongo-connector**

    sudo mongo-connector -m localhost:27017 -c mongo-connector_config.json  --namespace NOMDELABDD.*

    Mongo connector permet de repliquer les données présente dans mongoDB sur un cluster elasticsearch.

    Ci dessous un schema explicatif de son fonctionnement :

![Alt text](/Img_doc/Mongoconnector.png?raw=true)

**Organisation des bases de données:**

Ordar comporte 2 bases de données:

    - ORDAR
    - DOI
 La base Ordar contient plusieurs collections, une constante: Manual_Depot et plusieurs autres en fonction des projets importés avec Otelo-Cloud.
 
 La base DOI contient une seule collection, DOI, elle contient un document avec un ID ORDAR-DOI, un ID et l'etat du document(cet etat permet de gerer des accées concurrents, ainsi des lors qu'il est unlocked on peut utiliser la ressource). 


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

Cette classe permet de gérer les datasets , en creer , éditer, supprimer, generer un doi, envoyer un mail à un auteur.
Avant tout ajout d'un jeu de données ou modification d'un jeu existant, une verification de disponibilité du service Datacite est effectué.


-**FileController**: 

Cette classe permet d'effectuer des actions de telechargement, de preview en ligne de certains fichiers (extension disponible en preview : txt, png, jpg, gif, pdf.
Elle permet aussi un export des metadonnées en différents format: Datacite, Dublincore, JSON , BibTex.



**Détails des différentes module JS:**
    
   L'application est composé de six modules JS différents:
        
        - datatable (Affichage des resultats sous forme de pagination)
        - search (permet de rechercher un terme , par facets ou non)
        - mypublications (Affichage des publication de l'utilisateur courant sous forme de pagination, permet aussi une recherche par facets)
        - upload (permet de controller le formulaire upload et edit, rends dynamique le formualire et réalise les check de contenu Frontend)
        - preview (Affichage d'un modal permettant de visualiser un fichier dans une iframe)
        - send_email (Affichage d'un modal permettant d'envoyer un message a un auteur (contact depuis un jeu de données) ou au administrateur (contact depuis le footer de l'application)




**Aspect de générale de l’application :**

Pour l’aspect, le framework Semantic UI a été choisi pour sa simplicité d’utilisation et sa bonne documentation. Il permet de réaliser des interfaces graphiques responsives légère et rapide.

![Alt text](/Img_doc/Ordar_accueil.png?raw=true)




**Definition d'un jeu de données:**

Un jeu de données est un ensemble constitué de metadonnées et de fichiers.
Les metadonnées contiennent des champs obligatoires ainsi que facultatifs.
Un jeu de données à un acces défini: il peut etre :

    - Open(libre de consultation).
    - Closed(Seulement les metadonnées sont accessibles).
    - Embargoed(metadonnées accessibles mais pas les fichiers avant la date donner).
    - Unpublished (fichier importé avec un script d'import que le propriétaire peut publier quand il le souhaite).


**Modification du statut Embargoed -> Open:**
Le script Check_Embargoed_access.php doit etre exécuté une fois par jour afin de changer les statuts des jeux de données arrivé à échéance: des lors que la date du jour est égale à la date de l'embargo, le jeu de données est modifié en statut Open.


# Utilisation


**Rechercher un jeu de données:**
L'utilisateur peut effectuer une recherche par mot clé, il peut utiliser des opérateurs logique tels que AND et OR.
A l'issue de cette recherche l'utilisateur peut trier les données à l'aide de facets:
   
    -Sample kind
    -Authors
    -Keywords
    -Scientific fields
    -Languages
    -Filetypes
    -Access right
    -Date

Lors de la selection de plusieurs facets, l'opérateur de recherche est AND. 

**Insertion d'un nouveau jeu de données:**

L'utilisateur rempli le formulaire,il rempli les champs marqué d'une étoile rouge qui sont obligatoire, la verification est faite coté client et coté serveur.

Un numero de DOI perenne sera attribué au jeu de données.

Les informations sont ensuite traité et inserer en base de données.

Mongo connector se charge ensuite d'indexer ces données.

**Importation d'un jeu de données via OTELoCloud:**

Un script d'importation se charge d'importer les données présente dans un dossier spécifiques:
Lors de l'import celui ci importe dans les metadonnées le feuillet INTRO du fichier excel,
Le feuillet DATA est converti en fichier csv et il est joins à ce jeux de données comme fichier.

Le jeux de données est ajouter avec un statut Unpublished, il est accessible seulement pour son ou ses auteurs.
L'auteur peut choisir de le publier ou de le supprimer.


**Modification d'un jeu de données existant:**

L'utilisateur se rend sur la données a modifier, il clique sur edit, un formulaire apparait avec les metadonnées deja en base de données, l'utilisateur peut les modifier, il ne peut pas modifier ou ajouter des fichiers.


**Suppression d'un jeu de données:**

Un jeu de données peut etre supprimé si il a un statut unpublished, c'est a dire pas de DOI.
La suppression entraine la suppression TOTALE du jeu de données:

-Le fichier original

-Le csv genéré

-l'entrée en base de données



![Alt text](/Img_doc/Diagram_ORDAR.png?raw=true)



**Enregistrement des DOIs:**

![Alt text](/Img_doc/DOI_save.png?raw=true)




**Mode Administrateur:**

Il existe un mode administrateur qui permet de visualiser tout les jeux de données de tout le monde, même les données "Unpublished".
L'administrateur peut visualiser, modifier, et supprimer un jeu de données, même si celui ci a été publié.
Dans le cas ou l'administrateur supprime un jeu de données le DOI est alors désactivé.

L'administrateur peut aussi modifier et ajouter des fichiers, dans la limite qu'il doit avoir obligatoirement un fichier minimum lié aux metadonnées.

 














