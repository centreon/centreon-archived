=======================
Les serveurs satellites
=======================

************
Présentation
************

Les serveurs satellites (aussi appelé collecteurs) sont des serveurs de supervision équipés d'un ordonnanceur et d'un module broker. 
Ils sont chargés de superviser les équipements et de renvoyer les résultats vers le serveur Centreon central (pour la visualisation des résultats...).

*************
Mise en place
*************

Installation
============

Le processus d'installation est identique à celui d'un serveur Centreon central.
A la question **Which server type would you like to install ?** il faut choisir l'option **Poller server**.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07installpoller.png
   :align: center 

Configuration de l'ordonnanceur
===============================

Une fois l'installation réalisée, il faut intégrer ce satellite dans la configuration Centreon.

#. Rendez-vous dans le menu **Configuration** ==> **Centreon**
#. Dupliquez le fichier de configuration du serveur Central et éditez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

* Changez le **Nom du collecteur**.
* Entrez l'adresse IP du collecteur dans le champ **Adresse IP**.
* Activez le collecteur en cliquant sur **Activé** dans le champ **Statut**.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07addpoller.png
   :align: center

Maintenant, il est nécessaire de générer un fichier de configuration pour l'ordonnanceur Centreon Engine :

#. Rendez-vous dans le menu **Configuration** ==> **Moteurs de supervision**
#. Dans le menu de gauche, cliquez sur **main.cfg**
#. Dupliquez le fichier de configuration du collecteur **Central** et modifiez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

* Changez le **Nom de la configuration**.
* Activez le fichier de configuration de l'ordonnanceur en cliquant sur **Activé** dans le champ **Statut**.
* Choisissez le nouveau collecteur dans le champ **Collecteur lié**.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07mainconffile.png
   :align: center 

* Dans l'onglet **Données** - Champ **Multiple module broker** modifiez le nom du fichier de configuration de Centreon Broker **central-module.xml**. Par exemple : poller1-module.xml.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07mainconffilebrokerconf.png
   :align: center 

Configuration de Centreon Broker
================================

Il est nécessaire de générer un fichier de configuration pour le broker Centreon Broker :

#. Rendez-vous dans le menu **Configuration** ==> **Centreon**
#. Dans le menu de gauche, cliquez sur **Configuration** (en dessous de Centreon Broker)
#. Dupliquez le fichier de configuration du module de votre serveur central et éditez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

* Changez le **Nom** de la configuration.
* Modifiez le **Nom du fichier de configuration** qui doit correspondre au nom du fichier xml définit dans le fichier de configuration de l'ordonnanceur.
* Activez le fichier de configuration en cliquant sur **Activé** dans le champ **Statut**.
* Modifiez le champ **Collecteur** en sélectionnant votre nouveau collecteur.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07brokerconf.png
   :align: center 

* Dans l'onglet **Output**, modifiez le champ **Hôte distant** en entrant l'adresse IP du serveur contenant votre base MySQL (dans notre cas le serveur central).

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07brokerconfoutput.png
   :align: center 

Configuration de Centreontrapd
==============================

<<<<<<< HEAD
Il est nécessaire de modifier les fichiers de configuration de Centreontrapd afin que le service puisse interroger la base de données SQLLite. Plus d'informations au chapitre :ref:`Les traps SNMP <trapsnmp>`.
=======
Il est nécessaire de modifier les fichiers de configuration de Centreontrapd afin que le service puisse interroger la base de données SQLLite (voir chapitre :ref:`configuration_advanced_snmptrapds`).
>>>>>>> ede72c57e910b4b8906a3beb171f14c137558ba7

Synchronisation des sondes
==========================

Vous pouvez synchroniser les sondes entre votre serveur Central et vos serveurs satellites en utilisant l'utilitaire **rsync**.

.. warning::
   Ne pas réaliser cette action si vos sondes dépendent de librairies tierces devant au préalable être installées.

Echange de clés SSH
===================

Afin que le serveur central puisse exporter les fichiers de configuration du moteur de supervision, il est nécessaire d'effectuer un échange de clé SSH entre le serveur central et le nouveau serveur satellite.

Sur le serveur satellite :

#. Connectez-vous en tant que 'root'
#. Modifiez le mot de passe de l'utilisateur centreon :

::

	# passwd centreon

Sur le serveur central :

1. Connectez-vous en tant que 'centreon'

::

    # su - centreon

2. Si vous n'avez pas déjà généré une paire de clé publique/privée, tapez la commande suivante (laissez les options par défaut) :

::

	$ ssh-keygen
	
3. Puis exportez votre clé SSH vers le serveur satellite :

::

	$ ssh-copy-id -i /var/spool/centreon/.ssh/id_rsa.pub centreon@[ADRESSE_IP_DU_POLLER]

4. Vérifiez que vous pouvez vous connecter depuis le serveur central vers le serveur satellite en tant qu'utilisateur centreon. Vous pouvez utiliser la commande :

::

	$ ssh centreon@[ADRESSE_IP_DU_POLLER]

Exportation de la configuration
===============================

Il ne reste plus qu'à exporter la configuration afin de vérifier que l'installation du serveur satellite s'est bien déroulée.
