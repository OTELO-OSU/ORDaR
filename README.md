![Alt text](/Frontend/src/img/ordar.png?raw=true)


# Installation ORDaR Beta 1



**Prérequis :**

    -MongoDB 3.4.2

    -Elasticsearch 5.2

    -Mongo connector 

    -PHP 5.6

    -PHP-curl

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
    Rendez vous dans Frontend/config.ini
    UPLOAD_FOLDER défini ou les Uploads des utilisateurs vont être stockés, 
    choisissez un chemin et vérifier les permissions.
    Il s'agit du user qui a les droits d'écriture.
    Choisissez l’authentification de mongodb 
    host = 127.0.0.1
    port = 27017
    authSource= Le nom de votre BDD qui contiendra les jeux de données
    username = Le username de votre BDD qui contiendra les jeux de données
    password = Le mot de passe de votre BDD qui contiendra les jeux de données
    DOI_PREFIX = Votre prefix DOI datacite
    URL_DOI = votre URL d’enregistrement des DOIs
    DOI_database = Le nom de votre BDD qui contiendra le numéro de DOI
    user_doi = Le username de votre BDD qui contiendra le numéro de DOI
    password_doi = Le mot de passe de votre BDD qui contiendra le numéro de DOI
    admin[]= l'adresse mail des administrateurs avec des doubles quotes séparé d'une virgule
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


# Utilisation


**Rechercher un jeu de données:**
L'utilisateur peut effectuer une recherche par mot clé, il peut utiliser des opérateurs logique tels que AND et OR.
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

**Insertion d'un nouveau jeu de données:**

Avant tout dépot de jeu de données une vérification de disponibilité de l'API datacite est effectuée.

L'utilisateur rempli le formulaire,il rempli les champs marqués d'une étoile rouge qui sont obligatoires, la vérification est faite coté client et coté serveur.

Un numéro de DOI pérenne sera attribué au jeu de données.

Les informations sont ensuite traitées et insérées en base de données.

Mongo connector se charge ensuite d'indexer ces données.

L'utilisateur recevra un mail avec le DOI qui a été attribué au jeu de données.

**Importation d'un jeu de données via OTELoCloud:**

Un script d'importation se charge d'importer les données présentse dans un dossier spécifiques d'OTELoCloud:
Les métadonnées issues du feuillet INTRO du fichier excel sont importées dans la base Mongodb,
Le feuillet DATA est converti en fichier csv et il est joint à ce jeux de données comme fichier.

Le jeux de données est ajouté avec un statut Unpublished, il est accessible uniquement au propriétaire ou ses co-auteurs.
L'auteur peut choisir de le publier ou de le supprimer.

L'utilisateur recevra un mail avec le DOI qui a été attribué dés la publication du jeu de données. En statut unpublished l'id du fichier est provisoire (aucun DOI n'est attribué).

**Création d'un fichier brouillons dit "Draft":**
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

 













