# Organisation  


**Organisation des bases de données mongo:**

Ordar comporte 2 bases de données mongo:

    - ORDAR (le nom de votre Bdd: base de données principale)
    - DOI
    
 La base de données principale contient la collection: Manual_Depot 
 Cette collection contient les jeux de données déposés lors d'un dépot manuel (via upload)
 Dans le cas d'une implémentation des scripts de moissonage des jeux de données stockés sur l'espace collaboratif, d'autre collection pourront être créées du nom de ces projets. Les scripts de moissonage, spécifiques à OTELo sont disponibles sur demande.
 
 La base DOI contient une seule collection: DOI, elle contient un document avec un ID REPOSITORYNAME-DOI, un ID étant l’état du document (cet état permet de gérer des accès concurrents. Un système de verrou locked/unlocked est positionné pour pouvoir utiliser la ressource). 


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

Cette classe va permettre d'effectuer les requêtes vers l'api ElasticSearch et récupérer les données pour ensuite les envoyer vers datacite. Un mail est envoyé à l'administrateur en fin de traitement.


-**DatasheetController**: 

Cette classe permet de gérer les jeux de données , créer , éditer, supprimer, générer un doi, envoyer un mail à un auteur.
Avant un ajout d'un jeu de données ou modification d'un jeu existant, une vérification de disponibilité du service Datacite est effectué.



-**FileController**: 

Cette classe permet d'effectuer des actions de téléchargement, de preview en ligne de certains fichiers (extension disponible en preview : txt, png, jpg, gif, pdf.
Elle permet aussi un export des métadonnées en différents formats: Datacite, Dublincore, JSON , BibTex.

-**UserController**: 

Cette classe permet de gérer les comptes utilisateurs (Ajout, Suppression, Modifiation).

-**MailerController**: 

Cette classe permet d'envoyer des mails à des utilisateurs ou aux admins.

**Schéma base de données authentification**:

![Alt text](/Img_doc/schema_auth.png?raw=true)


**Détails des différentes module JS:**
    
   L'application est composée de six modules JS différents contenu dans le fichier search.js:
        
        - datatable (Affichage des résultats sous forme de pagination)
        - search (permet de rechercher un terme , par facets ou non)
        - mypublications (Affichage des publications de l'utilisateur courant, permet aussi une recherche par facettes)
        - upload (permet de controller le formulaire upload et edit, rends dynamique le formulaire et réalise les check de contenu Frontend)
        - preview (Affichage d'un modal permettant de visualiser un fichier dans une iframe)
        - send_email (Affichage d'un modal permettant d'envoyer un message à un auteur (contact depuis un jeu de données) ou aux administrateurs (contact depuis le footer de l'application)
	-changelog (Affichage sous forme de modal du changelog)
	-account (Verification Frontend des champs saisi)
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
	   SUPPLEMENTARY_FIELDS:Ajout de metadonné spécifiques par l'utilisateur
    DATA:
          FILES:
                DATA_URL:Denomination du fichier
                FILETYPE:Extension du fichier



