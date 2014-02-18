==============
Les traps SNMP
==============

**********
Définition
**********

Les traps SNMP sont des informations envoyées en utilisant le protocole SNMP depuis un équipement supervisé vers un serveur de supervision.
Ces informations contiennent plusieurs attributs dont :

* Adresse de l'équipement qui a envoyé l'information
* L'OID (Object Identifier) correspond à l'identifiant du message reçu
* Le message envoyé à travers la trap SNMP

Afin de pouvoir interpréter l'OID reçu, le serveur de supervision doit pouvoir savoir à quel évènement correspond l'OID reçu.
Pour cela, il doit disposer d'une base de données contenant les OID ainsi que leurs descriptions, c'est ce qu'on appelle les MIB.
Il existe deux types de MIB :

* Les MIB standards qui utilisent des OID standardisés et qui sont implémentés sur beaucoup d'équipements
* Les MIB constructeurs qui sont propres à des équipements particuliers

Les MIB constructeurs sont à récupérer auprès des contructeurs de matériels.
Au sein de Centreon, les OID sont stockées dans la base de données MySQL au sein d'un objet trap : à chaque trap correpond un message et une action précise.
Les traps sont ensuite reliées aux services passifs via l'onglet **Relations** du service.

************
Architecture
************

Avec Centreon 2.5.x, la gestion des traps SNMP a été complètement revue : l'architecture a été entièrement repensée. Deux nouveaux services entrent en jeu, centreontrapdforward et centreontrapd.

Traitement d'une trap par le serveur central
--------------------------------------------

Voici le processus de traitement d'une trap SNMP avec Centreon 2.5.x :

#. snmptrapd est le service permettant de récupérer les traps SNMP envoyées par les équipements : il écoute sur le port 162 UDP
#. Une fois la trap SNMP reçue, elle est envoyée au script centreontrapdforward qui va écrire les informations reçues dans un dossier de cache (par défaut : **/var/spool/centreontrapd/**)
#. Le service centreontrapd lit les informations reçues dans le dossier de cache et interprète les différentes traps reçues en vérifiant dans la base de données Centreon les actions à entreprendre pour traiter les traps reçues
#. Le service centreontrapd transmet les informations à Centreon Engine qui se charge d'interpréter la trap

[ TODO Récupérer les schémas de la version anglaise]

Traitement d'une trap par un serveur satellite
----------------------------------------------

Afin de garder une copie de la configuration des traps SNMP sur chaque serveur satellite, une base de données SQLLite est chargée de garder en cache les informations de traps contenues dans la base de données MySQL. 
Cette base de données SQLLite est automatiquement générée par le serveur Central. 
Voici le processus de traitement d'une trap SNMP avec Centreon 2.5.x :

#. snmptrapd est le service permettant de récupérer les traps SNMP envoyées par les équipements : il écoute sur le port 162 UDP
#. Une fois la trap SNMP reçue, elle est envoyée au script centreontrapdforward qui va écrire les informations reçues dans un dossier de cache (par défaut : **/var/spool/centreontrapd/**)
#. Le service centreontrapd lit les informations reçues dans le dossier de cache et interprète les différentes traps reçues en vérifiant dans la base de données SQLLite les actions à entreprendre pour traiter les traps reçues
#. Le service centreontrapd transmet les informations à Centreon Engine qui se charge d'interpréter la trap

[ TODO Récupérer les schémas de la version anglaise]

**************************
Configuration des services
**************************

Snmptrapd
---------

Afin d'appeller le script centreontrapdfoward, le fichier **/etc/snmp/snmptrapd.conf** doit contenir les lignes suivantes :

::

	disableAuthorization yes
	traphandle default su -l centreon -c "/usr/share/centreon/bin/centreontrapdforward"

Vous pouvez optimiser les performances de snmptrapd en utilisant les options suivantes :

* **-On** n'essaye pas de transformer les OIDs
* **-t** ne log pas les traps au serveur syslog
* **-n** n'essaye pas de transformer les adresses IP en nom d'hôtes

Ces options peuvent être modifiées dans le fichier **/etc/sysconfig/snmptrapd**

::

	OPTIONS="-On -d -t -n -p /var/run/snmptrapd.pid"

Il est également possible de placer le dossier de cache snmptrapd en mémoire vive. Pour cela, ajoutez la ligne suivante dans le fichier **/etc/fstab** :

::

	tmpfs /var/run/snmpd                     tmpfs defaults,size=128m 0 0

Centreontrapdforward
--------------------

Pour modifier le dossier de cache vers lequel les informations seront écrites, modifiez le fichier de configuration **/etc/centreon/centreontrapd.pm** :

::

	our %centreontrapd_config = (
		  spool_directory => '/var/spool/centreontrapd/',
	);

	1;

Vous pouvez également mapper le dossier dans le cache en mémoire vive, en ajoutant la ligne suivante dans le fichier **/etc/fstab** :

::

	tmpfs /var/spool/centreontrapd            tmpfs defaults,size=512m 0 0

Centreontrapd
-------------

Deux fichiers de configuration existent pour Centreontrapd :

* **/etc/centreon/conf.pm** contient les informations de connexion à la base de données
* **/etc/centreon/centreontrapd.pm** contient la configuration du service centreontrapd

Configuration du service
^^^^^^^^^^^^^^^^^^^^^^^^

Au sein du fichier **/etc/centreon/centreontrapd.pm** il est conseillé de modifier uniquement trois paramètres (si nécessaire):

* Si l'option **mode** est définie à 1 alors centreontrapd fonctionne sur un serveur satelite, sinon il fonctionne sur un serveur central
* L'option **centreon_user** permet de modifier l'utilisateur qui exécute les actions
* L'option **spool_directory** permet de modifier le dossier de cache à lire (si vous l'avez modifié dans le fichier de configuration de centreontrapdforward)

Configuration de la connexion à la base de données
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Il est possible de configurer le fichier **/etc/centreon/conf.pm** de deux manières :

* Conserver la connexion au serveur de base de données MySQL (nécessaire pour le serveur central et possible pour les serveurs satellites). Contenu du fichier :

::

	$centreon_config = {
	VarLib => "/var/lib/centreon",
	CentreonDir => "/usr/share/centreon/",
	"centreon_db" => "centreon",
	"centstorage_db" => "centreon_storage",
	"db_host" => "localhost:3306",
	"db_user" => "centreon",
	"db_passwd" => "centreon"
	};

	1;

* Connecter centreontrapd à la base de données SQLLite locale. Contenu du fichier :

::

	$centreon_config = {
	VarLib => "/var/lib/centreon",
	CentreonDir => "/usr/share/centreon/",
	"centreon_db" => "dbname=/etc/snmp/centreon_traps/centreontrapd.sdb",
	"centstorage_db" => "dbname=/etc/snmp/centreon_traps/centreontrapd.sdb",
	"db_host" => "",
	"db_user" => "",
	"db_passwd" => "",
	"db_type" => 'SQLite',
	};

	1;

***********************
Ajouter un constructeur
***********************

Au sein de Centreon, les OIDs sont classés par constructeur. Pour ajouter un constructeur :

#. Rendez-vous dans **Configuration** ==> **Traps SNMP**
#. Dans le menu de gauche, cliquez sur **Constructeur**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture]

* Les champs **Nom du constructeurs** et **Alias** définissent le nom et l'alias du constructeur
* Le champ **Description** fournit une indication sur le constructeur

*******************
Importation des MIB
*******************

Il est également possible d'importer des OIDs à partir des MIBs fournies par les constructeurs. Pour cela :

1. Rendez-vous dans **Configuration** ==> **Traps SNMP**
2. Dans le menu de gauche, cliquez sur **MIBs**

[ TODO Mettre une capture d'écran]

* La liste **Constructeur** permet de choisir le constructeur auquel appartient la MIB que vous importez
* Le champ **Fichier (mib)** permet de charger la MIB

3. Cliquez sur **Importer**

Notez bien : les dépendances des MIBS que vous importez doivent être présents dans le dossier **/usr/share/snmp/mibs**.

[ TODO Mettre une capture d'écran]

*******************************
Configuration avancée des traps
*******************************

Il est également possible de créer manuellement des OID :

#. Rendez-vous dans **Configuration** ==> **Traps SNMP**
#. Cliquez sur **Ajouter**

[ TODO Mettre une captre d'écran]

* Le champ **Nom du Trap** [TODO : Ce n'est pas plutôt "Champ de la trap" ?] définit le nom de la trap
* Le champ **OID** définit l'OID à recevoir pour que cette trap soit considérée comme reçue
* Le champ **Nom du constructeur** définit le nom du constructeur auquel appartient la trap
* Le champ **Message de sortie** contient le message à afficher en cas de réception d'une trap contenant l'OID configuré au-dessus.

Pour afficher le contenu de la trap on utilise la variable **$***. 
Le champ **Commentaires** (dernier champ) contient la liste des variables qui peuvent être affichées en cas de réception de la trap. Pour faire appel à ces variables, il faut utiliser : **$[Numéro de la variable]** [ TODO mettre une capture d'écran ?]

* Le champ **Statut par défaut** définit le statut par défaut du service en cas de réception de la trap
* Le champ **Default Severity** [ TODO Pas de traduction : traduction proposée] permet de définir un niveau de criticité par défaut
* Si la case **Mode de correspondance avancé** est cochée alors il est possible en fonction du message reçu de modifier le statut et la sévérité du statut
* Le champ **Disable submit result if no matched rules** [ TODO Pas de traduction : traduction proposée] désactive le traitement de la trap si le message reçu ne correspond à aucune règle avancée
* Une entrée de **Règles de correspondance avancées** permet d'ajouter une règle de correspondance qui modifie le statut et la criticité du service en fonction de l'expression régulière retrouvée dans la chaine
* Si la case **Envoyer le résultat** est cochée alors le résultat est soumis au moteur de supervision
* Si la case **Reprogrammer les services associés** est cochée alors le service sera controlé de manière active après la réception de la trap
* Si la case **Executer une commande spéciale** est cochée alors la commande définie dans **Commande spéciale** est exécutée

*************
Les variables
*************

Lors de l'ajout d'une règle de correspondance ou de l'exécution d'une commande spéciale il est possible de passer des arguments aux champs
**Chaine** ou **Commande spéciale**. Ces arguments sont listées dans le tableau ci-dessous :

+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
|   Nom de la variable     |   Description                                                                                                                             | 
+==========================+===========================================================================================================================================+
| @HOSTNAME@               | Nom d'hôte (dans Centreon) auquel le service est rattaché                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @HOSTADDRESS@            | Adresse IP de l'hôte ayant envoyé la trap                                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @HOSTADDRESS2@           | Nom DNS de l'hôte ayant envoyé la trap (si le serveur n'arrive pas à effectuer une résolution DNS inversée alors on récupère l'adresse IP |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @SERVICEDESC@            | Nom du service                                                                                                                            |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @TRAPOUTPUT@ ou @OUTPUT@ | Message envoyé par l'expéditeur de la trap                                                                                                |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @STATUS@                 | Statut du service                                                                                                                         |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @SEVERITYNAME@           | Nom du niveau de criticité                                                                                                                |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @SEVERITYLEVEL@          | Niveau de criticité                                                                                                                       |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @TIME@                   | Heure de réception de la trap                                                                                                             |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @POLLERID@               | ID du poller ayant reçu la trap                                                                                                           |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @POLLERADDRESS@          | Adresse IP du poller ayant reçu la trap                                                                                                   |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @CMDFILE@                | Chemin vers le fichier de commande de CentCore (central) ou de Centreon Engine (collecteur)                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+

*************************
Appliquer les changements
*************************

Pour pouvoir exporter les OID présents en base de données en fichier de configuration pour snmptrapd, suivez la procédure suivante :

#. Rendez-vous dans **Configuration** ==> **Traps SNMP**
#. Dans le menu de gauche, cliquez sur **Générer**
#. Sélectionnez le collecteur vers lequel vous souhaitez exporter les fichiers de configuration
#. Cochez **Generate trap database** [ TODO : Pas de traduction disponible] et **Appliquer la configuration**
#. Dans la liste déroulante **Send signal** [ TODO : Pas de traduction disponible] préférez l'option **Recharger**
#. Cliquez sur le bouton **Générer**
