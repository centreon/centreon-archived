====================================
Gestion des traps SNMP avec Centreon
====================================

*************************************
Recevoir des traps SNMP avec Centreon
*************************************

Ce sous-chapitre présente les différentes étapes afin de pouvoir superviser un équipement en utilisant les traps SNMP.

Importation des traps SNMP
==========================

Afin d'importer les traps SNMP, vous devez respecter les étapes suivantes :

#. Créez un constructeur lié à le trap SNMP que vous avez créé, voir :ref:`le sous-chapitre associé <configuration_advanced_snmptrapds_manufacturer>`
#. Importez la MIB au sein de l'interface web de Centreon, voir :ref:`le sous-chapitre associé <configuration_advanced_snmptrapds_mibimport>`

Lors de l'importation d'un fichier MIB, il est possible que des dépendances soient nécessaires durant l'import au niveau de votre serveur. Afin de pouvoir trouver les dépendances de vos MIB, il faut ouvrir votre fichier de MIB via un éditeur de texte standard, puis :

#. Repérez la ligne qui commence par IMPORTS
#. Toutes les dépendances nécessaires à l'importation de votre fichier de MIB se situent après le mot clé **FROM**

Exemple :

.. image :: /images/guide_exploitation/kdependances.png
   :align: center 

Dans le fichier de MIB montré ci-dessus, il existe 4 dépendances nécessaires à l'importation de la MIB : SNMPv2-SMI, SNMPv2-TC, SNMPv2-CONF, SNMP-FRAMEWORK-MIB.
Une fois l'importation terminée, il est nécessaire de modifier la définition du trap afin de modifier le statut par défaut du trap :

#. Rendez-vous dans le menu **Configuration > Traps SNMP**
#. Cliquez sur le trap que vous souhaitez modifier.

En fonction du message associé au trap, modifiez le statut par défaut du service. Dans le cas où le statut du service dépend du message reçu, utilisez le mode de correspondance avancé.

Créer un modèle de service passif
=================================

Afin de faciliter la configuration des services utilisant les traps SNMP, il est plus pratique de créer un modèle de service passif. De cette manière, lors de la création d'un service il ne restera plus qu'à faire hériter le service à partir de ce modèle et de lier le trap ou les traps SNMP associés à ce service.

#. Rendez-vous dans le menu **Configuration > Services**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

Le tableau ci-dessous résume l'ensemble des attributs d'un modèle de service passif :

+--------------------------------------+--------------------------------------------+
| Attributs                            | Description                                | 
+======================================+============================================+
| Onglet **Configuration du service**                                               |
+--------------------------------------+--------------------------------------------+
| Alias                                | TRAP	                                    |
+--------------------------------------+--------------------------------------------+
| Nom du service                       | generic-service-passif                     |
+--------------------------------------+--------------------------------------------+
| Période de contrôle                  | 24x7                                       |
+--------------------------------------+--------------------------------------------+
| Commande de vérification             | check_centreon_dummy                       |
+--------------------------------------+--------------------------------------------+
| Arguments                            | Status : 0                                 |
|                                      | Output : "Aucun trap reçu depuis 24 heures |
+--------------------------------------+--------------------------------------------+
| Nombre maximum de contrôle           | 1                                          |
+--------------------------------------+--------------------------------------------+
| Contrôles actifs activées            | Non                                        |
+--------------------------------------+--------------------------------------------+
| Contrôles passifs activées           | Oui                                        |
+--------------------------------------+--------------------------------------------+
| Onglet **Traitement des données**                                                 |
+--------------------------------------+--------------------------------------------+
| Contrôler la fraîcheur du résultat   | TRAP	                                    |
+--------------------------------------+--------------------------------------------+
| Seuil de fraicheur du résultat       | 86400 (24 heures)                          |
+--------------------------------------+--------------------------------------------+

.. note::
    La sonde check_centreon_dummy sera appelée si aucun trap n'est reçu sous 24 heures.

Création du service
===================

Puis, créez le service et associez ce dernier au modèle de service passif.
Il ne vous reste plus qu'à vous rendre dans l'onglet **Relations** et de renseigner, au sein du champ **Traps SNMP**, les traps SNMP qui pourront modifier le statut du service.

Maintenant, :ref:`régénérez les fichiers de configuration <configuration_advanced_snmptrapds_generate_configuration>` pour prendre en compte ces changements.

Simuler l'envoi d'un trap
=========================

Afin de tester que la réception des traps SNMP fonctionne correctement sur votre équipement. Vous pouvez envoyer un évènement SNMP fictif à votre serveur de supervision en utilisant l'utilitaire en ligne de commandes snmptrap.

Syntaxe : 

::
  
    snmptrap -v SNMP-VERSION -c COMMUNITY IP-DESTINATION UPTIME TRAP-OID PARAMETER-OID PARAMETER-TYPE PARAMETER-VALUE 

Avec :

* **SNMP-VERSION** : version du protocole SNMP. Pour la syntaxe ci-dessus, c'est obligatoirement 2c
* **COMMUNITY** : communauté SNMP
* **DESTINATION-IP** : IP de destination du trap SNMP. Cela peut être un poller ou le serveur Centreon
* **TRAP-OID** : OID contenant ENTERPRISE-OID plus l'OID du trap SNMP afin de former l'OID complet
* **UPTIME** : temps en secondes depuis le dernier redémarrage de l'équipement. Lorsque l'on précise une chaîne vide, cet argument est automatiquement rempli par le binaire « snmptrap »

Tout paramètre supplémentaire au trap SNMP doit contenir les 3 variables suivantes. Elles doivent être répétées pour chaque paramètre supplémentaire : 

* **PARAMETER-OID** : OID contenant ENTERPRISE-OID plus l'OID du trap SNMP afin de former l'OID du paramètre
* **PARAMETER-TYPE** : type de paramétré, 'i' pour « Integer », 's' pour « String », etc.
* **PARAMETER-VALUE** : valeur liée au paramètre. Mettre entre guillemets une chaîne de caractères contenant des espaces

Exemple de trap pour simuler un évènement "linkUp" sur l'interface 'eth0' :

::

    snmptrap -v2c -c public 192.168.1.1 '' .1.3.6.1.6.3.1.1.5.4 ifIndex i 2 ifDescr s eth0 ifAdminStatus i 1 ifOperStatus i 1


*****************************
Modifier le message de sortie
*****************************

Utiliser l'ensemble des arguments
=================================

Pour un trap SNMP, lors de la configuration du message de sortie, l'argument $* permet d'afficher l'ensemble des informations (valeur des arguments) contenu au sein du trap SNMP. Cependant, il est possible d'afficher uniquement certaines informations contenues au sein du trap SNMP en appelant unitairement les arguments.

Exemple : 

.. image :: /images/guide_exploitation/klinkexample.png
   :align: center

Le message de sortie "Link down on interface $2. State: $4." permet d'afficher uniquement le nom de l'interface et l'état de celle-ci (argument $2 et $4).

Où trouver les arguments ?

Les arguments se trouvent au sein de la documentation de la MIB de votre constructeur ou bien au sein du champ **Commentaires** de votre trap SNMP.

Par exemple :

.. image :: /images/guide_exploitation/klinkcomment.png
   :align: center

Pour afficher :

* L'index du lien tombé, utilisez l'argument $1
* Le nom de l'interface tombée, utilisez l'argument $2
* L'état administratif de l'interface, utilisez l'argument $3
* L'état de l'interface, utilisez l'argument $4

Par exemple, le message de sortie suivant permet d'afficher l'ensemble des arguments : 

::

    Link down on interface: $2 (index: $1). Operational state: $4, Administration state: $3


Effectuer un contrôle actif suite à la reception d'un trap
==========================================================

Il est possible par l'utilisation de l'option **Reprogrammer les services associés** de réaliser un contrôle actif sur le service suite à la réception du trap SNMP.

Le contrôle actif défini au niveau du service est alors effectué.

Executer une commande spéciale
==============================

Centreontrapd peut éxecuter une commande spéciale suite à la réception d'un trap SNMP. Pour utiliser cela, il suffit de cocher l'option **Executer une commande spécial** et d'écrire la commande voulue.


Utiliser l'ensemble des arguments (via les OID)
===============================================

Il est également possible de récupérer directement la valeur d'un argument sans connaître son ordre d'arrivée ($1, $2, $3, ...).
Pour cela, utilisez l'OID complet de l'argument.

Voici un exemple :

::

    Link down on interface: @{.1.3.6.1.2.1.2.2.1.2} (index: @{.1.3.6.1.2.1.2.2.1.1}). Operational state: @{.1.3.6.1.2.1.2.2.1.8}, Administration state: @{.1.3.6.1.2.1.2.2.1.7}

Utiliser une variable externe
=============================

Il est également possible de modifier le message de sortie en récupérant des informations via des scripts ou commandes externes et de récupérer le résultat pour l'insérer au sein du message.
Pour cela, au sein de la définition de votre trap SNMP, rendez-vous dans l'onglet **Avancé** et ajoutez une (ou plusieurs) commande(s) PREEXEC.

Exemple :

.. image :: /images/guide_exploitation/kpreexec.png
   :align: center

La première commande est "snmpget -v 2c -Ovq -c public @HOSTADDRESS@ ifAlias.$1" et permet de récupérer l'alias de l'interface. La variable "$1" correspond ici à la valeur associée à l'argument 1 des traps linkUp/linkDown, soit l'index.

La seconde commande contient "snmpget -v 2c -Ovq -c public @HOSTADDRESS@ ifSpeed.$1" et permet de récupérer la vitesse de l'interface. La variable "$1" correspond ici à la valeur associée à l'argument 1 des traps linkUp/linkDown, soit l'index.

Pour utiliser le résultat de la première commande dans le message de sortie, utilisez l'argument $p1 et pour utiliser le résultat de la seconde commande dans le message de sortie, utilisez l'argument $p2.

Par conséquent, nous pouvons déduire le message de sortie suivant : 

::

    Link down on interface: $2 (index: $1). Operational state: $4, Administration state: $3, Alias : $p1, Speed : $p2

Utiliser une expression régulière
=================================

Il est également possible de modifier le message de sortie en utilisant une expression régulière par l'intermédiaire de l'option **Output Transform**. Il suffit de renseigner une expression régulière et elle sera éxécutée à la réception d'un trap SNMP.

Par exemple :

::

    s/\|/-/g

Remplacera les occurences **|** dans le message de sortie du trap SNMP par **-**. 


********************************
Router/transférer les traps SNMP
********************************

Parfois, il existe un concentrateur de traps SNMP au sein d'une société. Exemple : Oracle GRID.
Oracle GRID est chargé de fédérer les informations de tous les serveurs Oracle en cas de nécessité, c'est le serveur Oracle GRID qui envoie un trap SNMP au serveur de supervision.

Or, à partir d'un trap SNMP reçu par Oracle GRID, on souhaite pouvoir extraire l'adresse IP de l'hôte concerné et afficher le message du trap dans un service appartenant non pas à Oracle Grid mais à l'hôte concerné par le trap (le véritable émetteur).

Pour cela, exécutez la procédure suivante :

1. Créez un trap générique, ayant les paramètres suivants : 

+-----------------------------------+--------------------------------------------+
| Attributs                         | Description                                | 
+===================================+============================================+
| Onglet **Configuration du trap**                                               |
+-----------------------------------+--------------------------------------------+
| Nom                               | Nom du trap                                |
+-----------------------------------+--------------------------------------------+
| Mode                              | Unique                                     |
+-----------------------------------+--------------------------------------------+
| OID                               | OID du trap                                |
+-----------------------------------+--------------------------------------------+
| Statut                            | Statut par défaut du trap                  |
+-----------------------------------+--------------------------------------------+
| Message de sortie                 | Message de sortie personnalisé             |
+-----------------------------------+--------------------------------------------+
| Onglet **Avancé**                                                              |
+-----------------------------------+--------------------------------------------+
| Activé le routage                 | Cochez la case	                         |
+-----------------------------------+--------------------------------------------+
| Commande de routage               | $2 (ici on part du principe que l'argument |
|                                   | numéro 2 du trap contient l'adresse IP     |
|                                   | de l'hôte concerné par le trap)            |
+-----------------------------------+--------------------------------------------+

2. Créer une deuxième définition du trap avec :

+--------------------------------------+---------------------------------------------------------+
| Attributs                            | Description                                             | 
+======================================+=========================================================+
| Onglet **Configuration du trap**                                                               |
+--------------------------------------+---------------------------------------------------------+
| Nom                                  | Nom du trap (autre que celui de la première définition) |
+--------------------------------------+---------------------------------------------------------+
| OID                                  | OID du trap (même que celui de la première définition)  |
+--------------------------------------+---------------------------------------------------------+
| Statut                               | Statut par défaut du trap                               |
+--------------------------------------+---------------------------------------------------------+
| Message de sortie                    | Message de sortie personnalisé                          |
+--------------------------------------+---------------------------------------------------------+

3. Associer la première définition à un service (par exemple PING) du serveur Oracle GRID

4. Associer la deuxième définition à un service passif de l'hôte concerné

5. Générer les définitions de traps SNMP et redémarrer centreontrapd

Au sein du champ **Commande de routage** vous pouvez utiliser les arguments suivants : 

+----------------------+-------------------------------------------------------------------------------------------------------------+
|   Nom de la variable |   Description                                                                                               |
+======================+=============================================================================================================+
| @GETHOSTBYADDR($2)@  | Résolution DNS inverse permettant de connaitre le nom DNS à partir de l'adresse IP (127.0.0.1 -> localhost) |
+----------------------+-------------------------------------------------------------------------------------------------------------+
| @GETHOSTBYNAME($2)@  | Résolution DNS permettant de connaitre l'adresse IP à partir du nom DNS (localhost -> 127.0.0.1)            |
+----------------------+-------------------------------------------------------------------------------------------------------------+

Ne pas soumettre le trap SNMP durant un downtime
================================================

L'option **Check Downtime** permet à centreontrapd de contrôler si le service n'est pas dans un statut de downtime lors de la réception du  trap SNMP. Il est possible alors d'annuler la soumission du trap.

.. note::

    Ce mode de focntionnement n'est compatible qu'avec Centreon Broker et des services supervisés depuis le central.

Il est possible d'adapter le comportement selon ces trois méthodes :

* Aucun : Rien de spécial, le trap SNMP est envoyé normalement
* Real-Time : Si un downtime est actif sur le service, il n'est pas mis à jour.
* History : Ooption utilisée pour ne pas prendre en compte un trap SNMP  qui concerne un événement passé lors d'un temps d'arrêt.


***
FAQ
***

Comme vu dans le chapitre sur :ref:`les traps SNMP <configuration_advanced_snmptrapds>`, plusieurs éléments entrent en jeu dans la gestion des traps SNMP.
En cas de problèmes, il est nécessaire de vérifier le bon fonctionnement de son architecture, plusieurs points sont à vérifier.

Source: Gestion des traps SNMP sous Centreon par Laurent Pinsivy, GNU/Linux Magazine N°160 - Mai 2013, licence CC BY-NC-ND

Configuration de l'émetteur
===========================

Le premier point à contrôler est la configuration de l'équipement ou application qui a émis l'interruption que vous auriez dû recevoir. Vérifiez l'adresse IP ou nom DNS de destination, la communauté SNMP ainsi que la version du protocole.

Pare-feux réseau et logiciels, routage
======================================

Le second point à contrôler sont les autorisations des pare-feux réseau et logiciels ou la mise en place d'un routage spécifique. Si un ou plusieurs pare-feux réseau sont présent ou si une translation de port et/ou d'adresse IP est en place, vérifiez que le flux est possible entre l'émetteur et le collecteur.
L'utilisation de sondes réseau, de débogage des équipements réseau (pare-feux et routeurs) ou des logiciels tcpdump/wireshark sur le collecteur peut vous permettre de valider la réception du flux de données sur le port UDP 162.

Snmptrapd
=========

Une fois la réception du flux validé, vérifiez l'état de fonctionnement du processus snmptrapd, qui doit être en cours d'exécution, ainsi que ses options de configuration. 
Il est possible d'activer la journalisation du processus. Pour cela modifiez le fichier « /etc/sysconfig/snmptrapd.options » et remplacez la ligne « OPTIONS » pour avoir :

::

	# snmptrapd command line options
	# OPTIONS="-On -d -t -n -p /var/run/snmptrapd.pid"
	OPTIONS="-On -Lf /var/log/snmptrapd.log -p /var/run/snmptrapd.pid"

Redémarrez le processus pour prendre en compte les modifications. Ainsi, pour toute réception de traps SNMP, ces évènements seront inscrit dans le journal « /var/log/snmptrapd.log ». Si les évènements sont inscrit dans le journal, supprimez la journalisation et passez à l'étape suivante.

Dans le cas où vous filtrez par communauté SNMP, vérifiez les communautés autorisées dans le fichier de configuration « /etc/snmp/snmptrapd.conf ». Si après toutes ces vérifications les traps SNMP ne sont pas inscrites dans le journal, vérifiez que le processus écoute sur le port UDP 162 pour les équipements distants en utilisant la commande :

::

    # netstat -ano | grep 162 
    udp        0      0 0.0.0.0:162             0.0.0.0:*                           off (0.00/0/0)

Si tel n'est pas le cas, modifiez le port d'écoute du processus.

.. note::
	On ne le répète jamais assez mais désactivez le débogage du processus après validation du fonctionnement. Dans le cas contraire, la volumétrie des journaux peut être très importante.
	
Centreontrapdforward
====================

Une fois la validation du processus snmptrapd réalisée, contrôlez le processus centreontrapdforward. La première étape consiste à vérifier l'appel de ce processus par snmptrapd dans le fichier « /etc/snmp/snmptrapd.conf » :

* Vérifier que le service snmptrapd appelle bien centreontrapdforward. Pour cela, éditez le fichier **/etc/snmp/snmptrapd.conf** et vérifiez que le fichier contient :

::

    traphandle default su -l centreon -c "/usr/share/centreon/bin/centreontrapdforward"

Si l'accès au fichier est incorrect, modifiez le et redémarrez le processus snmptrapd.
Vous pouvez contrôler le bon fonctionnement du binaire centreontrapdforward en vous rendant au chapitre de configuration de :ref:`centreontrapdforward<configuration_advanced_centreontrapdforward>`.

Centreontrapd
=============

Le prochain binaire est celui de Centreon qui permet de sélectionner l'hôte possédant l'adresse IP ou le nom DNS de l'émetteur ainsi que le service lié à cet hôte et auquel est reliée la définition de l'interruption SNMP. Pour vérifier son fonctionnement, il convient de vérifier les paramètres de configuration de centreontrapd.

Vous pouvez vérifier la bonne configuration de centreontrapd au sein du chapitre de configuration de :ref:`centreontrapd<configuration_advanced_centreontrapd>`.


CentCore
========

Dans le cas d'un serveur central, le processus Centcore doit être démarré pour transférer la commande externe à l'ordonnanceur supervisant l'émetteur, vérifiez son état de fonctionnement. Activez le débogage du processus via le menu **Administration > Options > Débogage** et redémarrez le processus.

.. note::
    Vous pouvez modifier le niveau de débogage du processus via le fichier **/etc/sysconfig/centcore** en modifiant la sévérité.

En cas de non réception de la commande externe, vérifiez le chemin d'accès au fichier de commande du processus défini dans la variable « $cmdFile » du fichier de configuration « /etc/centreon/conf.pm .». Le chemin doit être « /var/lib/centreon/centcore.cmd » dans le cas d'un serveur central ou le chemin vers le fichier de commande de l'ordonnanceur.

Ordonnanceur
============

Que vous ayez configuré un serveur central ou un collecteur distant pour la réception de trap SNMP, l'ordonnanceur doit recevoir la commande externe de changement de statut et/ou de message de sortie («output»). Vérifiez le journal de l'ordonnanceur. Dans le cas de Centreon Engine le fichier est **/var/log/centreon-engine/centengine.log**. Les lignes suivantes doivent apparaître :

::


[1352838428] EXTERNAL COMMAND: PROCESS_SERVICE_CHECK_RESULT;Centreon-Server;Traps-SNMP;2;Probleme critique
[1352838433] PASSIVE SERVICE CHECK: Centreon-Server;Traps-SNMP;2;Probleme critique

Si seule la commande externe apparaît mais pas la prise en compte de celle-ci par l'ordonnanceur (« PASSIVE SERVICE CHECK »), il se peut qu'un problème de synchronisation de l'horloge système soit en cause. Le serveur est soit en retard et la commande sera traitée ultérieurement, soit en avance et la commande ne sera pas prise en compte.

Centreon
========

Afin d'être visible dans Centreon, l'ordonnanceur doit transmettre les informations, via son module NEB, à la partie serveur du broker pour que ce dernier l'insère en base de données. Centreon affichera ensuite le résultat à partir de la base de données « centreon_storage ».
S'il vous est possible de visualiser les informations des derniers contrôles de votre collecteur dans l'interface web, alors vous devriez voir le statut et le message de sortie (« output ») de modifiés. Si tel n'est pas le cas, alors votre ordonnanceur n'est pas connecté à la partie serveur de votre broker. Les problèmes peuvent être les suivants :

* L'ordonnanceur n'a pas chargé le module NEB à son démarrage car celui-ci est introuvable ou non défini dans les options de l'ordonnanceur
* Le module NEB n'a pu se connecter à la partie serveur à cause d'un problème de paramétrage.
* Un pare-feu bloque la connexion entre le collecteur et le serveur Centreon qui héberge la base de données -La partie serveur du broker n'est pas fonctionnelle ou n'est pas en cours d'exécution

Schéma détaillé
===============

Vous trouverez ci-dessous un schéma détaillé de tous les processus utilisés et/ou présents lors de la réception d'une interruption SNMP :

.. image :: /images/guide_exploitation/kcentreontrapd_schema.png
   :align: center
