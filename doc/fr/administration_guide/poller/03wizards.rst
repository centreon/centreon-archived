.. _centreon_broker_wizards:

================================================
Configuration de Centreon Broker via l'assistant
================================================

Vous pouvez créer des configurations de Centreon Broker via l'assistant de configuration, pour cela :
#. Allez dans le menu **Configuration ==> Collecteurs ==> Centreon-Broker ==> Configuration**

Deux choix sont disponibles :

.. image:: /images/guide_utilisateur/configuration/10poller/centreon_broker_wizard.png
   :align: center


**********************************
Configuration d'un serveur central
**********************************

.. image:: /images/guide_utilisateur/configuration/10poller/centreon_broker_wizard_02_schema.png
   :align: center
   :alt: Distributed monitoring schema

.. note::
   Schéma d'une architecture distribuée

#. Choisissez le type **Central**
#. Entrez un nom pour la configuration

.. image:: /images/guide_utilisateur/configuration/10poller/centreon_broker_wizard_02_step02.png
   :align: center

**************************************************************
Configuration d'un collecteur pour une architecture distribuée
**************************************************************

.. warning::
    Pour cette configuration vous devez au préalable avoir installé un collecteur.

.. image:: /images/guide_utilisateur/configuration/10poller/centreon_broker_wizard_03_schema.png
   :align: center
   :alt: Schema distributed monitoring

.. note::
   Schéma d'une architecture distribuée


#. Choisissez **Collecteur uniquement**
#. Entrez un nom pour la configuration
#. Sélectionnez un collecteur
#. Entrez l'adresse IP ou le nom DNS **FQDN** du serveur central

.. image:: /images/guide_utilisateur/configuration/10poller/centreon_broker_wizard_03_step02.png
   :align: center
