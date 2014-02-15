=======================
Les serveurs satellites
=======================

************
Présentation
************

Les serveurs satellites (aussi appellé collecteurs) sont des serveurs équipés de moteur de supervision. Ils sont chargés de superviser les équipements et de renvoyer les
résultats vers le serveur Centreon central (pour la visualition des résultats...).

*************
Mise en place
*************

Installation
------------

L'installation se déroule de la même manière qu'un serveur central à l'exception d'un paramètre :
A la question **Which server type would you like to install ?** il faut choisir l'option **Poller server**.

Configuration de l'ordonnanceur
-------------------------------

Dans un premier temps, nous allons ajouter un nouveau serveur de supervision.

#. Rendez-vous dans **Configuration** ==> **Centreon**
#. Dupliquez le fichier de configuration du serveur Central et modifiez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

[ TODO Mettre une capture d'écran]

* Changez le **Nom du collecteur**
* Entrez l'adresse IP du collecteur dans le champ **Adresse IP**
* Activez le collecteur en cliquant sur **Activé** dans le champ **Statut**

Maintenant, il est nécessaire de générer un fichier de configuration pour l'ordonnanceur :

#. Rendez-vous dans **Configuration** ==> **Moteurs de supervision**
#. Dans le menu de gauche, cliquez sur **main.cfg**
#. Dupliquez le fichier de configuration du collecteur **Central** et modifiez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

[ TODO Mettre une capture d'écran]

* Changez le **Nom de la configuration**
* Activez le fichier de configuration de l'ordonnanceur en cliquant sur **Activé** dans le champ **Statut**
* Choisissez le nouveau collecteur dans le champ **Collecteur lié**
* Dans l'onglet **Données** - Champ **Multiple module broker** modifiez le nom du fichier de configuration de Centreon Broker **central-module.xml**. Par exemple : poller1-module.xml

Configuration de Centreon Broker
--------------------------------

Il est nécessaire de générer un fichier de configuration pour Centreon Broker :

#. Rendez-vous dans **Configuration** ==> **Centreon**
#. Dans le menu de gauche, cliquez sur **Configuration** (en dessous de Centreon Broker)
#. Dupliquez le fichier de configuration du module de votre serveur central et modifiez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

[ TODO Mettre une capture d'écran]

* Changez le **Nom** de la configuration
* Modifiez le **Nom du fichier de configuration** qui doit correspondre au nom du fichier xml définit dans le fichier de configuration de l'ordonnanceur
* Activez le fichier de configuration en cliquant sur **Activé** dans le champ **Statut**
* Modifiez le champ **Collecteur** en sélectionnant votre nouveau collecteur
* Dans l'onglet **Output**, modifiez le champ **Host to connect to** [ TODO Pas de traduction disponible] en entrant l'adresse IP du serveur contenant votre base MySQL (dans notre cas le serveur central)

Configuration de Centreontrapd
------------------------------

Il est nécessaire de modifier les fichiers de configuration de Centreontrapd afin que le service puisse interroger la base de données SQLLite (voir le chapitre précédent).

Synchronisation des sondes
--------------------------

Vous pouvez synchroniser les sondes entre votre serveur Central et vos serveurs satellites en utilisant l'utilitaire **rsync**.

Echange de clés SSH
-------------------

Afin que le serveur central puisse exporter les fichiers de configuration du moteur de supervision, il est nécessaire d'effectuer un échange de clé SSH entre le serveur central et le nouveau serveur satellite.

Sur le serveur satellite :

#. Connectez-vous en tant que root
#. Modifiez le mot de passe de l'utilisateur centreon :

::

	# passwd centreon

Sur le serveur central :

1. Connectez-vous en tant que centreon
2. Si vous n'avez pas déjà généré une paire de clé publique/privée, tapez la commande suivante (laissez les options par défaut) :

::

	$ ssh-keygen
	
3. Puis exportez votre clé SSH vers le serveur satellite :

::

	$ ssh-copy-id -i /var/spool/centreon/.ssh/id_rsa.pub centreon@[ADRESSE_IP_DU_POLLER]

4. Vérifiez que vous pouvez vous connecter depuis le serveur central vers le serveur satellite en tant qu'utilisateur centreon. Vous pouvez utiliser la commande :

::

	$ ssh centreon@[ADRESSE_IP_DU_POLLER]