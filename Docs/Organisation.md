# Organisation  


**Organisation des bases de données:**

Ordar comporte 2 bases de données:

    - ORDAR
    - DOI
    
 La base Ordar contient plusieurs collections, une depot manuel : Manual_Depot et plusieurs autres en fonction des projets importés avec ordar_script.
 
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

-**UserController**: 

Cette classe permet d'effectuer de gérer les compte utilisateurs (Ajout, Suppression, Modifiation).

-**MailerController**: 

Cette classe permet d'envoyer de smail à des utilisateurs ou aux admins.

-**Schéma base de données authentification**:

![Alt text](/Img_doc/schema_auth.png?raw=true)


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
