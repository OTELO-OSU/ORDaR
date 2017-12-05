# Utilisation  

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


**Rechercher un jeu de données:**
L'utilisateur peut effectuer une recherche par mot clé, il peut utiliser des opérateurs logique tels que AND et OR.
Une recherche peut etre effectue uniquement sur un champs spécifique, voici une liste des champs interogeable et comment effectué cette recherche:
    - TITLE : INTRO.TITLE:"mot clé à chercher"
    - DESCRIPTION : INTRO.DATA_DESCRIPTION:"mot clé à chercher"
    - AUTEURS : INTRO.FILE_CREATOR.DISPLAY_NAME:"mot clé à chercher"
    - LANGAGE : INTRO.LANGUAGE:"mot clé à chercher"
    
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

**Importation d'un jeu de données via Ordar script (OTELoCloud):**

OTELO utilise des canevas afin que les chercheurs puissent créés des fichiers de données interroperable.

Un script d'importation se charge d'importer les données présente dans un dossier spécifiques d'OTELoCloud:
Les métadonnées issues du feuillet INTRO du fichier excel sont importées dans la base Mongodb,
Le feuillet DATA est converti en fichier csv et il est joint à ce jeux de données comme fichier.

Vous trouverer le script d'importation ici : https://bitbucket.org/arnouldpy/ordar_scripts/
Le repository est privée, il sera necessaire de nous envoyer un message afin de répondre à votre demande.

Le jeux de données est ajouté avec un statut Unpublished, il est accessible uniquement au propriétaire ou ses co-auteurs.
L'auteur peut choisir de le publier ou de le supprimer.
Si le jeu de données est supprimé ou publié, le fichier xlsx est automatiquement supprimé du repertoire source.

L'utilisateur recevra un mail avec le DOI qui a été attribué dés la publication du jeu de données. En statut unpublished l'id du fichier est provisoire (aucun DOI n'est attribué).

**Création d'un fichier brouillon dit "Draft":**

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
    DATA:
          FILES:
                DATA_URL:Denomination du fichier
                FILETYPE:Extension du fichier
