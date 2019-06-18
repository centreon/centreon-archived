.. _basic_plugins:

==================================
Principe de base de la supervision
==================================

Avant de commencer à superviser, voyons ensemble quelques notions principales :

* Un **hôte** (ou **host** en anglais) est tout équipement qui possède une adresse IP et que l'on souhaite
  superviser : un serveur physique, une machine virtuelle, une sonde de température, une caméra IP, une imprimante
  ou un espace de stockage, par exemple.
* Un **service** est un point de contrôle, ou indicateur, à superviser sur un hôte. Cela peut être le taux
  d'utilisation du CPU, la température, la détection de mouvement, le taux d'utilisation de la bande passante, les
  E/S disque, etc.
* Afin de mesurer chaque indicateur, on utilise des **sondes** de supervision (**plugin** en anglais) qui sont
  exécutées périodiquement par un moteur de collecte appelé **Centreon Engine**.
* Pour être exécutée, une sonde a besoin d'un ensemble d'arguments qui définissent par exemple à quel hôte se
  connecter ou via quel protocole. La sonde et ses arguments associés forment une **commande** (**command** en anglais).
  
Ainsi, superviser un hôte avec Centreon consiste à configurer l'ensemble des commandes nécessaires à la mesure des
indicateurs désirés, puis à déployer cette configuration sur le moteur de collecte afin que ces commandes soient
exécutées périodiquement.

Néanmoins, pour simplifier drastiquement la configuration on s'appuyera avantageusement sur des modèles de supervision :

* Un **modèle d'hôte** (**host template** en anglais) définit la configuration des indicateurs pour un type
  d'équipement donné.
* Il s'appuie sur des **modèles de service** (**service templates**) qui définissent la configuration des commandes
  nécessaires à la mesure de ces indicateurs.
* Centreon fournit des **Plugins Packs** téléchargeables à installer sur sa plateforme de supervision: chaque Plugin
  Pack regroupe modèles de hôte et de services pour configurer en quelques clics la supervision d'un équipement
  particulier.

Ce guide de démarrage rapide propose d'installer les modèles de supervision fournis gratuitement avec la solution
Centreon puis de les mettre en oeuvre pour superviser vos premiers équipements. 

.. image:: /images/quick_start/host_service_command.png
    :align: center

.. note::
    Pour aller plus loin avec les modèles de configuration, lisez le chapitre :ref:`Les Modèles<hosttemplates>`.

***********************************************
Installation des modèles de supervision de base
***********************************************

Rendez-vous dans le menu **Configuration > Plugin Packs**.

.. note::
    Avant toute chose, appliquez la procédure de :ref:`configuration du proxy<impproxy>` pour configurer et vérifier
    la connexion de votre serveur Centreon à Internet.

Commencez par installer le Plugin Pack **base-generic** en déplaçant votre curseur sur ce dernier et en cliquant sur
l'icône **+** (il s'agit d'un pré-requis à l'installation de tout autre Plugin Pack) :

.. image:: /_static/images/quick_start/pp_base_generic.png
    :align: center

Installez ensuite les Plugin Packs inclus gratuitement avec la solution, par exemple **Linux SNMP** et **Windows SNMP** :

.. image:: /_static/images/quick_start/pp_install_basic.gif
    :align: center

Vous disposez maintenant des modèles de base pour configurer votre supervision !

Cinq Plugin Packs supplémentaires sont débloqués après vous être inscrit sur `notre site web <https://store.centreon.com>`_
et plus de 300 packs sont disponibles si vous souscrivez à `l'offre IMP <https://store.centreon.com>`_.

.. note::
    Si vous avez déjà un compte Centreon, `vous pouvez maintenant authentifier votre plate-forme  
    <https://documentation-fr.centreon.com/docs/plugins-packs/en/latest/installation.html>`_
    afin de recevoir ces Plugin Packs additionnels ainsi que tout autre service associé à votre
    compte.

**************************
Déployez votre supervision
**************************

Commencez dès à présent à supervisez vos premiers équipements :

* :ref:`Superviser un serveur Linux en SNMP<monitor_linux>`
* :ref:`Superviser un serveur Windows en SNMP<monitor_windows>`
* :ref:`Superviser un routeur Cisco en SNMP<monitor_cisco>`
* :ref:`Superviser une base de données MySQL ou MariaDB<monitor_mysql>`
* :ref:`Superviser une imprimante en SNMP<monitor_printer>`
* :ref:`Superviser un onduleur en SNMP<monitor_ups>`
