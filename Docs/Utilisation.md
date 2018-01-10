# Utilisation  

**Création / Gestion des utilisateurs:**

l'application propose une procédure d'inscription : "sign up"

  ![Alt text](/Img_doc/signup.png?raw=true)

Celle-ci permet de faire son inscription avec validation de la demande par token mail, un mail de création est envoyé aux administrateurs de l'application.

Une fois votre compte créé, il doit être activé par un administrateur qui a été précedement notifié.
Une fois activé, vous pouvez déposer des jeux de données sur la plateforme

A noter : vous pouvez modifier votre profil via : "myaccount" et pourquoi pas saisir votre orcid_id ci cela n'a pas été fait lors de votre inscription.

**Rechercher un jeu de données:**

L'utilisateur peut effectuer une recherche par mot clé, il peut utiliser des opérateurs logique tels que AND et OR.
Une recherche peut etre effectuée uniquement sur un champs spécifique, voici une liste des champs interogeable et comment effectué cette recherche:

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
    
Seules les facettes Access right ont l’opérateur OR

Lors de la sélection de plusieurs facettes, l'opérateur de recherche est AND. 

**Statut d'un jeu de données:**

Un jeu de données est un ensemble constitué de métadonnées et de fichiers données.
Les métadonnées contiennent des champs obligatoires et facultatifs.
Un jeu de données à un accès défini: il peut être :

    - Open(libre de consultation).
    - Closed(Seulement les métadonnées sont accessibles).
    - Embargoed(métadonnées uniquement accessibles, les fichiers seront publié et disponible à une date fournie par le publiant).
    - Unpublished (fichier importé de l'espace collaboratif de stockage avec un script d'import que le propriétaire peut publier quand il le souhaite suivant les trois modes précedent).

NOTE : Modification du statut Embargoed -> Open:

Le script Check_Embargoed_access.php doit être exécuté une fois par jour afin de changer les statuts des jeux de données arrivé à échéance: des lors que la date du jour est égale à la date de l'embargo, le jeu de données est modifié en statut Open.

**Ajout d'un nouveau jeu de données:**

Aller dans l'onglet : "Upload"

Avant tout dépot de jeu de données une vérification de disponibilité de l'API datacite est effectuée.

L'utilisateur rempli le formulaire, il rempli les champs obligatoire (marqués d'une étoile rouge), la vérification est faite coté client et coté serveur.

Un numéro de DOI sera attribué au jeu de données.

Les informations sont ensuite traitées et insérées en base de données.

L'utilisateur recevra une confirmation par mail de son dépot avec le DOI qui a été attribué au jeu de données.

**Création d'un fichier brouillon dit "Draft":**

L'utilisateur peut créer un fichier brouillons s'il ne souhaitent pas aller jusqu'au bout de son dépot.
Pour cela, il doit renseigner au minimum le titre ainsi que le langage.
Une fois créé le fichier apparait dans la section "my dataset" uniquement pour l'utilisateur qui a créé ce jeu de données, il peut l'éditer puis le publier ou le supprimer à tout moment.

**Modification d'un jeu de données existant :**

La modification d'un jeu de données concerne uniquement les métadonnées de celui-ci.
L'utilisateur se rend sur le jeu de donnée à modifier, il clique sur edit, un formulaire apparaît avec les métadonnées présentes en base de données, l'utilisateur peut les modifier, il ne peut pas modifier ou ajouter des fichiers.


**Suppression d'un jeu de données:**

Un jeu de données peut être uniquement supprimé si il a un statut unpublished ou draft, c'est à dire sans DOI.
La suppression entraîne la suppression TOTALE du jeu de données:

-Le fichier original 

-l'entrée en base de données

dans le cas de l'implémentation du service de moissonage OTELo harvester-geo-stations (cf ci-après)
le fichier original et le CSV de l'espace collaboratif de stockage sont supprimés.

![Alt text](/Img_doc/Diagram_ORDAR.png?raw=true)


**Importation d'un jeu de données via  harvester-geo-stations (OTELoCloud) Spécifique à OTELo:**

OTELO utilise des canevas (CSV) afin que les chercheurs puissent créer des fichiers de données interroperables.

Un script d'importation se charge d'importer les données présentes dans le dossier de l'espace collaboratif de stockage:
Les métadonnées issues du feuillet INTRO du fichier excel sont importées dans la base Mongodb,
Le feuillet DATA est converti en fichier csv et il est joint à ce jeux de données comme fichier.

Vous trouverer le script d'importation ici : https://bitbucket.org/arnouldpy/harvester-geo-stations/
Le repository est privée, vous pouvez nous contacter pour plus de détails.

Le jeux de données est ajouté avec un statut Unpublished à la  base de données, il est accessible uniquement pour le propriétaire ou ses co-auteurs.
L'auteur peut choisir de le publier ou de le supprimer.
Si le jeu de données est supprimé ou publié, le fichier xlsx (de l'espace collaboratif de stockage) est automatiquement supprimé du repertoire source. Dans le cas d'une publication, le jeu de données supprimé de l'espace collaboratif de stockage est remplacé par un fichier Html contenant un lien vers le jeu de données publié sur l'entrepot.

De plus, lors de la publication, l'utilisateur recevra un mail avec le DOI qui a été attribué au jeu de données. 

NB: En statut unpublished l'id du fichier est provisoire (aucun DOI n'est attribué).



** NOTES DIVERSES : **

***Enregistrement des DOIs:***

L'enregistrement des DOIs nécessite un compte sur https://mds.datacite.org/ (pour la france, contacter http://wwww.inist.fr)

![Alt text](/Img_doc/DOI_save.png?raw=true)


***Mode Administrateur:***

Il existe un mode administrateur qui permet de visualiser tous les jeux de données, même les données "Unpublished".
L'administrateur peut visualiser, modifier, et supprimer un jeu de données, même si celui ci a été publié.
Dans le cas ou l'administrateur supprime un jeu de données le DOI est alors désactivé.

L'administrateur peut aussi modifier et ajouter des fichiers (avec un minimum de 1 fichier par jeux de données)


