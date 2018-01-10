![Alt text](/Frontend/src/img/logo.png?raw=true)

# ORDaR

Sommaire:
=================
* [Présentation](#presentation)
* [Fonctionalités](#Fonctionalités)
* [Installation (non docker)](/Docs/Installation.md)
* [Installation docker](/Docs/docker.md)
* [Utilisation](/Docs/Utilisation.md)
* [Organisation (code et Bdd)](/Docs/Organisation.md)



# Présentation <a name="presentation"></a>
ORDaR est un entrepôt de données développé par et pour OTELo, toutefois celui-ci est entièrement personnalisable et peut être déployé dans votre organisation.
A l'installation, vous pouvez choisir le nom de votre entrepot grâce au fichier de configuration detaillé dans la rubrique : [Installation](/Docs/Installation.md) mais nous vous invitons à choisir la solution de déploiement via [Docker](#docker).
Vous pouvez ainsi déployer facilement une instance de cet entrepot pour votre institution.

ORDaR offre un environnement sécurisé et pérenne (sous réserve du contrat de service de votre hebergeur et des procédures de sauvegarde mises en place) pour le dépôt de jeux de données et permet d'attribuer un DOI (Digital Object Identifier) pour vos publication ou datapaper (sous reserve de posseder un compte chez datacite, pour la france cf [INIST](http://www.inist.fr) ).


		Cet entrepôt vise à promouvoir l'accès ouvert: valoriser et partager les connaissances.


**Aspect général de l’application :**

![Alt text](/Img_doc/Ordar_accueil.png?raw=true)
![Alt text](/Img_doc/ordar_search.png?raw=true)


# Fonctionalités <a name="Fonctionalité"></a>

- Deux profils utilisateurs :
	* Admin (droit de lecture et ecriture sur tous les jeux de données)
	* User (droit de lecture et ecriture sur les jeux de données déposés par le User)

- Possiblité de lier son compte avec son Orcid id ![Alt text](/Img_doc/orcid2_id.png?raw=true)

- Dépots de jeux de données suivant différents niveaux de droit d'accès:

	* Ouvert: accès total (opendata)
	* Fermé: Accès restreint aux seules métadonnées
	* Embargo: accès restreint aux métadonnées seulement jusqu'à la date de publication fixée par le producteur du jeux de données
	 
	 
- Attribution d'une licence Creative Common v4 sur les jeux de données publié. ![Alt text](/Img_doc/cc2_icon.png?raw=true)

- Attribution d'un DOI (Digital Object Identifier) pour vos jeux de données publié. ![Alt text](/Img_doc/datacite2.png?raw=true)

- Signalement des jeux de données via les réseaux sociaux : ![Alt text](/Img_doc/fb3_icon.png?raw=true) ![Alt text](/Img_doc/tweeter2_icon.png?raw=true) ![Alt text](/Img_doc/linkedin2_icon.png?raw=true)

- Mise à jour possible des métadonnées de vos jeux de données (avec suivi des modifications)

- Accessiblité des métadonnées dans les normes internationales, téléchargeables en 4 formats:

	*  Datacite
	*  BibteX
	*  JSON
	*  DublinCore
	 
- Prévisualisation des jeux de données (pour les formats ascii, pdf, jpg, png)

- Visualisation cartographique (dans le cas données geospatialisées)

- Suivi du nombre de téléchargement des jeux de données. ![Alt text](/Img_doc/download2.png?raw=true)

