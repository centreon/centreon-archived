.. _configuration_advanced_snmptrapds:

==============
Les traps SNMP
==============

**********
Définition
**********

Les traps SNMP sont des informations envoyées en utilisant le protocole SNMP depuis un équipement supervisé vers un serveur de supervision (satellite).
Ces informations contiennent plusieurs attributs dont :

* Adresse de l'équipement qui a envoyé l'information.
* L'OID racine (Object Identifier) correspond à l'identifiant du message reçu.
* Le message envoyé au travers du trap SNMP qui correspond à un ensemble de paramètres (1 à N).

Afin de pouvoir interpréter l'évènement reçu, le serveur de supervision doit posséder dans sa configuration le nécessaire pour traduire l'évènement.
Pour cela, il doit disposer d'une base de données contenant les OID ainsi que leurs descriptions, c'est ce qu'on appelle les fichiers MIB.
Il existe deux types de MIB :

* Les MIB standards qui utilisent des OID standardisés et qui sont implémentés par de nombreux constructeurs sur leurs équipements.
* Les MIB constructeurs qui sont propres à chacun et souvent à chaque modèle d'équipement.

Les MIB constructeurs sont à récupérer auprès des constructeurs de matériels.
Centreon permet de stocker la définition des traps SNMP dans sa base de données MySQL.
Les traps peuvent ensuite être reliés à des services passifs via l'onglet **Relations** de la définition d'un service.

************
Architecture
************

Avec Centreon 2.5.x, la gestion des traps SNMP a été revue en profondeur par rapport aux versions précédentes : 

*   les processus 'snmptt' et 'centtraphandler' ont été fusionnés au sein d'un unique processus 'centreontrapd'.
*   le processus 'snmptthandler' est remplacé par le processus 'centreontrapdforward'.
*   les satellites peuvent disposer de leur propre définition de Trap SNMP au sein d'une base dédiée SQLite supprimant ainsi l'accès au serveur MySQL Centreon.

Traitement d'un trap par le serveur central
===========================================

Voici le processus de traitement d'un trap SNMP avec Centreon 2.5.x :

#. snmptrapd est le service permettant de récupérer les traps SNMP envoyés par les équipements (par défaut il écoute sur le port **UDP 162**).
#. Une fois le trap SNMP reçu, il est envoyé au script 'centreontrapdforward' qui va écrire les informations reçues dans un dossier tampon (par défaut : **/var/spool/centreontrapd/**).
#. Le service 'centreontrapd' lit les informations reçues dans le dossier tampon et interprète les différents traps reçus en vérifiant dans la base de données Centreon les actions à entreprendre pour traiter ces évènements.
#. Le service 'centreontrapd' transmet les informations à l'ordonnanceur ou au service 'centcore' (pour transmettre les informations à un ordonnanceur distant) qui se charge de modifier le statut et les informations associées au service auquel est lié le trap SNMP.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06_trap_centreon.png
   :align: center

Traitement d'un trap par un serveur satellite
=============================================

Afin de garder une copie de la configuration des traps SNMP sur chaque serveur satellite, une base de données SQLite est chargée de garder en cache les informations de traps contenues dans la base de données MySQL. 
Cette base de données SQLite est automatiquement générée par le serveur Central. 
Voici le processus de traitement d'un trap SNMP avec Centreon 2.5.x :

#. snmptrapd est le service permettant de récupérer les traps SNMP envoyées par les équipements (par défaut il écoute sur le port **UDP 162**).
#. Une fois le trap SNMP reçu, il est envoyé au script 'centreontrapdforward' qui va écrire les informations reçues dans un dossier tampon (par défaut : **/var/spool/centreontrapd/**).
#. Le service 'centreontrapd' lit les informations reçues dans le dossier tampon et interprète les différentes traps reçus en vérifiant dans la base de données SQLite les actions à entreprendre pour traiter les traps reçus.
#. Le service 'centreontrapd' transmet les informations à l'ordonnanceur qui se charge de modifier le statut et les informations associées au service dont est lié le trap SNMP.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06_trap_poller.png
   :align: center

.. note::
    Le processus Centcore à la charge, comme pour l'export de configuration de la supervision, de copier la base SQLite sur le collecteur distant.

**************************
Configuration des services
**************************

Snmptrapd
=========

Afin d'appeler le script 'centreontrapdfoward', le fichier **/etc/snmp/snmptrapd.conf** doit contenir les lignes suivantes :

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

Il est également possible de placer le dossier tampon snmptrapd en mémoire vive. Pour cela, ajoutez la ligne suivante dans le fichier **/etc/fstab** :

::

	tmpfs /var/run/snmpd                     tmpfs defaults,size=128m 0 0

.. _configuration_advanced_centreontrapdforward:

Centreontrapdforward
====================

Pour modifier le dossier tampon vers lequel les informations seront écrites, modifiez le fichier de configuration **/etc/centreon/centreontrapd.pm** :

::

	our %centreontrapd_config = (
		  spool_directory => '/var/spool/centreontrapd/',
	);

	1;

Vous pouvez également mapper le dossier dans le cache en mémoire vive, en ajoutant la ligne suivante dans le fichier **/etc/fstab** :

::

	tmpfs /var/spool/centreontrapd            tmpfs defaults,size=512m 0 0

.. _configuration_advanced_centreontrapd:

Centreontrapd
=============

Deux fichiers de configuration existent pour Centreontrapd :

* **/etc/centreon/conf.pm** contient les informations de connexion à la base de données MySQL
* **/etc/centreon/centreontrapd.pm** contient la configuration du service centreontrapd

Configuration du service
------------------------

Au sein du fichier **/etc/centreon/centreontrapd.pm** il est conseillé de modifier uniquement trois paramètres (si nécessaire) :

* Si l'option **mode** est définie à 1 alors centreontrapd fonctionne sur un serveur satellite, sinon il fonctionne sur un serveur central (Centreon).
* L'option **centreon_user** permet de modifier l'utilisateur qui exécute les actions.
* L'option **spool_directory** permet de modifier le dossier tampon à lire (si vous l'avez modifié dans le fichier de configuration de 'centreontrapdforward').

Voici un exemple de configuration possible du fichier **/etc/centreon/centreontrapd.pm** (le fichier de configuration peut être modifiée avec '-config-extra = xxx') :

::

    our %centreontrapd_config = (
        # Temps en secondes avant d'arrêter brutalement les sous processus
        timeout_end => 30,
        spool_directory => "/var/spool/centreontrapd/",
        # Délai entre deux contrôles du répertoire de "spool" pour détecter de nouveaux fichiers à traiter
        sleep => 2,
        # 1 = utiliser la date et heure du traitement e l'évènement par centreontrapdforward
        use_trap_time => 1,
        net_snmp_perl_enable => 1,
        mibs_environment => '',
        remove_backslash_from_quotes => 1,
        dns_enable => 0,
        # Séparateur à appliquer lors de la substitution des arguments
        separator => ' ',
        strip_domain => 0,
        strip_domain_list => [],
        duplicate_trap_window => 1,
        date_format => "",
        time_format => "",
        date_time_format => "",
        # Utiliser le cache d'OID interne de la base de données
        cache_unknown_traps_enable => 1,
        # Temps en secondes avant de recharger le cache
        cache_unknown_traps_retention => 600,
        # 0 = central, 1 = poller
        mode => 0,
        cmd_timeout => 10,
        centreon_user => "centreon",
        # 0 => continuer en cas d'erreur MySQL | 1 => ne pas continuer le traitement (blocage) en cas d'erreur MySQL
        policy_trap => 1,
        # Enregistrement des journaux en base de données
        log_trap_db => 0,
        log_transaction_request_max => 500,
        log_transaction_timeout => 10,
        log_purge_time => 600
    );
    
    1;

Configuration de la connexion à la base de données
--------------------------------------------------

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

* Connecter centreontrapd à la base de données SQLite locale. Contenu du fichier :

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

**********************
Configuration Centreon
**********************

.. _configuration_advanced_snmptrapds_manufacturer:

Ajouter un constructeur
=======================

Au sein de Centreon, les OIDs racines des traps SNMP sont classés par constructeur. Pour ajouter un constructeur :

#. Rendez-vous dans le menu **Configuration** ==> **Traps SNMP**
#. Dans le menu de gauche, cliquez sur **Constructeur**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06constructors.png
   :align: center 

* Les champs **Nom du constructeur** et **Alias** définissent le nom et l'alias du constructeur
* Le champ **Description** fournit une indication sur le constructeur

.. _configuration_advanced_snmptrapds_mibimport:

Importation des MIB
===================

Il est également possible d'importer des OIDs à partir des MIBs fournies par les constructeurs. Pour cela :

1. Rendez-vous dans le menu **Configuration** ==> **Traps SNMP**
2. Dans le menu de gauche, cliquez sur **MIBs**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06importmibs.png
   :align: center 

* La liste **Constructeur** permet de choisir le constructeur auquel appartient la MIB que vous importez
* Le champ **Fichier (mib)** permet de charger la MIB

3. Cliquez sur **Importer**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06importmibssuccess.png
   :align: center 

.. note::
   Les dépendances des MIBS que vous importez doivent être présentes dans le dossier **/usr/share/snmp/mibs**.
   Une fois l'import terminé, supprimez les dépendances préalablement copiées.

.. note::
   Une fois les traps SNMP importés, il est nécessaire de vérifier le statut "Supervision" associé aux évènements. Par défaut celui-ci sera "OK".

Configuration manuelle des traps
================================

Configuration basique
---------------------

Il est également possible de créer manuellement des définitions de trap SNMP :

#. Rendez-vous dans le menu **Configuration** ==> **Traps SNMP**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06addsnmptrap.png
   :align: center

* Le champ **Nom du Trap** définit le nom du trap.
* Le champ **OID** définit l'OID racine à recevoir pour que ce trap soit considéré comme reçu.
* Le champ **Nom du constructeur** définit le nom du constructeur auquel appartient le trap à sélectionner dans la liste déroulante.
* Le champ **Message de sortie** contient le message à afficher en cas de réception d'un trap contenant l'OID configuré au-dessus.

.. note::
   Par défaut, la MIB contient la définition de cette variable (Exemple : "Link up on interface $2. State: $4.", ici $2 sera remplacé par le 2ème argument reçu dans l'évènement.). Dans le cas contraire, la variable **$*** permet d'afficher l'ensemble des arguments contenu dans le trap.

.. note::
   Il est possible de construire soit même le message de sortie. Pour cela, utilisez la MIB afin de connaitre les arguments qui seront présents dans le corps de l'évènement et récupérer les arguments avec les variables **$n**. Chaque argument étant identifié par un OID, il est possible d'utiliser directement cet OID afin de le placer dans le message de sortie sans connaitre sa position via la variable **@{OID}**.

* Le champ **Statut par défaut** définit le statut "supervision" par défaut du service en cas de réception du trap.
* Le Si la case **Envoyer le résultat** est cochée alors le résultat est soumis au moteur de supervision.
* Le champ **Commentaires** (dernier champ) contient par défaut le commentaire constructeur du trap SNMP. La plupart du temps, ce commentaire indique la liste des variables contenues dans le trap SNMP (voir chapitre suivant sur la configuration avancée).

Configuration avancée des traps
-------------------------------

Il est possible de détermine le statut d'un service à partir de la valeur d'un paramètre du trap SNMP plutôt qu'à partir de l'OID racine. Anciennement les constructeurs définissaient
un trap SNMP (OID racine) par type d'évènement à envoyer (linkUp / linkDown). Aujourd'hui, la tendance est de définir un OID racine par catégorie d'évènements puis de définir l'évènement via un ensemble de paramètres.

Pour cela, il est possible de définir des **Règles de correspondance avancées** en cliquant sur le bouton |navigate_plus| et de créer autant de règles que nécessaire.
Pour chaque règle, définir les paramètres :

*   **Chaine** définit l'élément sur lequel sera appliqué la recherche (@OUTPUT@ défini l'ensemble du **Message de sortie** traduit).
*   **Expression régulière** définit la recherche de type REGEXP à appliquer.
*   **Statut** définit le statut du service en cas de concordance.

.. note::
   L'ordre est important dans les règles de correspondance car le processus s'arrêtera à la première règle dont la correspondance est assurée.

* Le champ **Ne pas envoyer le résultat si pas de correspondance avérée** désactive l'envoi des informations au moteur d'ordonnancement si aucune correspondance avec une règle n'est validée.

* Si la case **Reprogrammer les services associés** est cochée alors le prochain contrôle du service, qui doit être 'actif', sera reprogrammé au plus tôt après la réception du trap.
* Si la case **Exécuter une commande spéciale** est cochée alors la commande définie dans **Commande spéciale** est exécutée.

Configuration très avancée des traps
------------------------------------

L'onglet **Avancé** permet de configurer le comportement d'exécution du processus de traitement des traps SNMP lors de la réception de ce dernier.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/06advancedconfiguration.png
   :align: center

*   **Activer le routage** permet d'activer le routage des informations.
*   **Commande de routage** permet de définir la commande à utiliser pour le routage.

Avant d'exécuter le traitement de l'évènement (traduction du **Message de sortie**), il est possible d'exécuter une commande appelée PREEXEC.
Pour cela, il est possible de définir des **Commande PREEXEC (de type SNMPTT)** en cliquant sur le bouton |navigate_plus| et de créer autant de règles que nécessaire.

*   **Définition de la commande PREEXEC** définit la commande à exécuter.

Voici un exemple d'utilisation avec le trap linkUP :
Pour un équipement Cisco, $2 == ifDescr contient le numéro de port de l'interface (GigabitEthernet0/1 par exemple). 
La meilleure description de l'interface est contenue dans le champ SNMP ifAlias.

La commande suivante permet de récupérer cette valeur

::

    snmpget -v 2c -Ovq -c <community> <cisco switch> ifAlias.$1

Pour utiliser le résultat de la commande PREEXEC dans le **Message de sortie**, il faut utiliser la variable $p{n} où 'n' correspond à l'ordre de définition de la commande.
Exemple

::

    "Interface $2 ( $p1 ) linkUP. State: $4." "$CA"

Le résultat sera de la forme : Interface GigabitEthernet0/1 ( SERVEUR NAS ) linkUP. State: up

*   Le champ **Enregistrer les informations des traps SNMP en base de données** permet de journaliser ou non les traps en base de données.
*   Le champ **Temps d'exécution maximum** exprimé en secondes, permet de définir le temps maximum de traitement de l'évènement y compris les commandes de prétraitement (PREEXEC) ainsi que celles de post-traitement (commande spéciale).
*   Le champ **Intervalle d'exécution** exprimé en secondes, permet de définir le temps minimum d'attente entre deux traitements d'un évènement.
*   Le champ **Type d'exécution** permet d'activer l'**Intervalle d'exécution** en définissant les conditions **Par OID racine**, **Par la combinaison OID racine et hôte** ou de désactiver cette restriction **Aucune**.
*   Le champ **Méthode d'exécution** permet de définir si lors de la réception de plusieurs mêmes évènements (OID racine). L'exécution est soit **Séquentielle**, soit **Parallèle**.

*************
Les variables
*************

Lors de l'ajout d'une règle de correspondance ou de l'exécution d'une commande spéciale il est possible de passer des arguments aux champs
**Chaine** ou **Commande spéciale**. Ces arguments sont listés dans le tableau ci-dessous :

+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
|   Nom de la variable     |   Description                                                                                                                             | 
+==========================+===========================================================================================================================================+
| @{NUMERIC_OID}           | Récupération de la valeur d'un argument via son OID, exemple @{.1.3.6.1.4.1.9.9.43.1.1.1}                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| $1, $2...                | Récupération de la valeur d'un argument via son ordre d'apparition                                                                        |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| $p1, $p2,...             | Valeur de la commande PREEXEC ($p1 = pour la première commande, $p2 pour la seconde, ...)                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| $*                       | Tous les arguments séparés par un espace                                                                                                  |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @HOSTNAME@               | Nom d'hôte (dans Centreon) auquel le service est rattaché                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @HOSTADDRESS@            | Adresse IP de l'hôte ayant envoyé le trap                                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @HOSTADDRESS2@           | Nom DNS de l'hôte ayant envoyé le trap (si le serveur n'arrive pas à effectuer une résolution DNS inversée alors on récupère l'adresse IP |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @SERVICEDESC@            | Nom du service                                                                                                                            |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @TRAPOUTPUT@ ou @OUTPUT@ | Message envoyé par l'expéditeur du trap                                                                                                   |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @STATUS@                 | Statut du service                                                                                                                         |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @SEVERITYNAME@           | Nom du niveau de criticité de l'évènement                                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @SEVERITYLEVEL@          | Niveau de criticité de l'évènement                                                                                                        |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @TIME@                   | Heure de réception du trap                                                                                                                |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @POLLERID@               | ID du collecteur ayant reçu le trap                                                                                                       |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @POLLERADDRESS@          | Adresse IP du collecteur ayant reçu le trap                                                                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| @CMDFILE@                | Chemin vers le fichier de commande de CentCore (central) ou de Centreon Engine (collecteur)                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+

De plus, il existe des variables spéciales pouvant être utilisées dans la section **Paramètres de routage** au niveau de la **Commande de routage** si l'option **Activer le routage** est sélectionnée : 

+----------------------+-------------------------------------------------------------------------------------------------------------+
|   Nom de la variable |   Description                                                                                               |
+======================+=============================================================================================================+
| @GETHOSTBYADDR($1)@  | Résolution DNS inverse permettant de connaitre le nom DNS à partir de l'adresse IP (127.0.0.1 -> localhost) |
+----------------------+-------------------------------------------------------------------------------------------------------------+
| @GETHOSTBYNAME($1)@  | Résolution DNS permettant de connaitre l'adresse IP à partir du nom DNS (localhost -> 127.0.0.1)            |
+----------------------+-------------------------------------------------------------------------------------------------------------+

.. _configuration_advanced_snmptrapds_generate_configuration:

*************************
Appliquer les changements
*************************

Pour pouvoir exporter les OID présents en base de données en fichier de configuration pour centreontrapd, suivez la procédure suivante :

#. Rendez-vous dans le menu **Configuration** ==> **Traps SNMP**
#. Dans le menu de gauche, cliquez sur **Générer**
#. Sélectionnez le collecteur vers lequel vous souhaitez exporter les fichiers de configuration
#. Cochez **Générer la base de données des traps** et **Appliquer la configuration**
#. Dans la liste déroulante **Envoyer le signal** (préférez l'option **Recharger**)
#. Cliquez sur le bouton **Générer**

.. |navigate_plus|	image:: /images/navigate_plus.png
