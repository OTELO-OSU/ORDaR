

#Installation ORDaR Beta 1



**Prérequis :**

    -MongoDB 3.4.2

    -Elasticsearch 5.2

    -Mongo connector 

    -PHP 5.6

    -PHP-dev

    -PHP-curl

    -PHP-xml

    -PHP-pear

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

    Installer php-dev
    sudo apt-get install php5.6-dev
    sudo apt-get install php5.6-xml
    et pear
    sudo apt-get install php-pear
    ensuite :
    sudo pecl install mongodb
    et ajouter 
    extension=mongo.so dans  php.ini d’apache2 et du CLI

**Configuration apache2**

    activer mode rewrite :
    sudo a2enmod rewrite

    Modifier la configuration apache:
    <Directory "/var/www/html">
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>

**Demarrer la base mongo en mode replica set :**
    
    sudo mongod --replSet "rs0"

    Démarré shell mongo et exécuter :
        rs.initiate()

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
    Choisissez l’authentification de mongodb 
    host = 127.0.0.1
    port = 27017
    username =
    password =

**Dans votre fichier de configuration apache ajoutez :**

     <Directory #CHEMIN PRECEDEMMENT CHOISI>
           Options Indexes FollowSymLinks
            AllowOverride None
            Order allow,deny
            Allow from 127.0.0.1
            Require all granted
        </Directory>

    Alias /download  #CHEMIN PRECEDEMMENT CHOISI

**Lancez Mongo-connector**

    mongo-connector -m  mongodb://username:password@localhost:27017 -t localhost:9200 -d elastic2_doc_manager -c mongo-connector_config.json   


