Configuration de l'ordonnanceur
===============================

Une fois l'installation réalisée, il faut intégrer ce satellite dans la configuration Centreon.

#. Rendez-vous dans le menu **Configuration** ==> **Collecteurs**
#. Dupliquez le fichier de configuration du serveur Central et éditez-le
#. Modifiez les paramètres suivants, puis sauvegardez :

* Changez le **Nom du collecteur**.
* Entrez l'adresse IP du collecteur dans le champ **Adresse IP**.
* Activez le collecteur en cliquant sur **Activé** dans le champ **Statut**.

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/07addpoller.png
   :align: center

#. Rendez-vous dans le menu **Configuration** ==> **Collecteur** ==> **Moteur de supervision**
#. Selectionnez la configuration qui correspond au dernier poller ajouté
#. Modifiez les paramètres suivants, puis sauvegardez :

* Dans l'onglet **Données** - Champ **Multiple module broker** modifiez le nom du fichier de configuration de Centreon Broker **central-module.xml**. Par exemple : poller1-module.xml.

.. image:: /images/guide_utilisateur/configuration/10advanced_configuration/07mainconffilebrokerconf.png
   :align: center

Configuration de Centreon Broker
================================

Il est nécessaire de générer un fichier de configuration pour le broker Centreon Broker :

#. Rendez-vous dans le menu **Configuration** ==> **Collecteurs** ==> **Centreon-Broker** ==> **Configuration**
#. Utilisez **Ajouter avec l'assistant**
#. Choisissez **Collecteur uniquement**
#. Indiquez un nom de configuration ainsi que l'adresse du serveur central de supervision
#. Cliquez sur **Finish**

.. image:: /images/guide_utilisateur/configuration/10advanced_configuration/07brokerconfwizzard.png
   :align: center


Authentification avec Centreon Broker (optionnel)
=================================================

Si vous souhaitez authentifier les pollers envoyant des données dans
votre système de monitoring vous pouvez utiliser le mécanisme
d'authentification intégré à Centreon Broker. Celui-ci est basé sur
l'utilisation de certificats X.509.

La première étape est de générer un certificat pour l'autorité de
certification (Certificate Authority, CA) avec OpenSSL. *ca.key* sera la
clé privée (à stocker de manière sécurisée), tandis que *ca.crt* sera la
clé publique servant à authentifier les connexions entrantes.

::

	$> openssl req -x509 -newkey rsa:2048 -nodes -keyout ca.key -out ca.crt -days 365


Nous pouvons maintenant générer les certificats en utilisant la clé de la CA.

::

	$> openssl req -new -newkey rsa:2048 -nodes -keyout central.key -out central.csr -days 365
	$> openssl req -new -newkey rsa:2048 -nodes -keyout poller.key -out poller.csr -days 365
	$> openssl x509 -req -in central.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out central.crt -days 365 -sha256
	$> openssl x509 -req -in poller.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out poller.crt -days 365 -sha256


Placez *central.key*, *central.crt* et *ca.crt* sur le serveur Centreon
central (dans **/etc/centreon-broker** par exemple) et *poller.key*,
*poller.crt* et *ca.crt* sur votre poller.

Nous devons maintenant configurer Centreon Broker pour utiliser ces
fichiers. Allez dans **Configuration ==> Pollers ==> Broker configuration**.
Pour *central-broker-master*, dans l'onglet *Input*, vous devez remplir les
paramètres suivants pour *central-broker-master-input*.

- Enable TLS encryption = Yes
- Private key file = /etc/centreon-broker/central.key
- Public certificate = /etc/centreon-broker/central.crt
- Trusted CA's certificate = /etc/centreon-broker/ca.crt

.. image:: /_static/images/configuration/broker_certificates.png
   :align: center

De manière similaire pour le poller, vous devez modifier les paramètres
de la connexion TCP dans l'onglet Output.

- Enable TLS encryption = Yes
- Private key file = /etc/centreon-broker/poller.key
- Public certificate = /etc/centreon-broker/poller.crt
- Trusted CA's certificate = /etc/centreon-broker/ca.crt

Regénérez la configuration des pollers affectés par ces changements
(**Configuration ==> Pollers**) et la mise en place de
l'authentification est terminée.


Configuration de Centreontrapd
==============================

Il est nécessaire de modifier les fichiers de configuration de Centreontrapd afin que le service puisse interroger la base de données SQLite (voir chapitre :ref:`configuration_advanced_snmptrapds`).

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

.. note::
    Référez-vous à la documentation :ref:`Déployer la configuration<deployconfiguration>`
