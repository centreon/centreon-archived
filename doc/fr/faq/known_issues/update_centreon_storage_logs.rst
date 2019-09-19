.. _update_centreon_storage_logs:

=============================================
Mise à jour de la table centreon_storage.logs
=============================================

L’objectif de cette procédure est de permettre la modification de la colonne log_id de la table centreon_storage.logs sans interruption de service.

Prérequis
=========

Mot de passe
------------

Avant de pouvoir exécuter le script il est nécessaire de vous munir du mot de passe root de la base de données Centreon.

Activation de PHP pour Centreon 19.10 (**Centos7**)
---------------------------------------------------

Sur la nouvelle version 19.10 de Centreon (Centos7) il est nécessaire d’activer PHP avant de pouvoir lancer des scripts PHP en ligne de commande.

La commande est la suivante:
::

# scl enable rh-php72 bash

Explications
============

La mise à jour de la table se déroulera de la façon suivante:

1. renommage de la table **centreon_storage.logs** en **centreon_storage.logs_old**
2. création de la nouvelle table **centreon_storage.logs**
3. migration des données par partition

Mise à jour
===========

Sur une installation classique de Centreon, le script se situe à l’emplacement suivant :
::

# usr/share/centreon/tools/update_centreon_storage_logs.php

Exécution en mode interactif (<10 millions d’enregistrements)
-------------------------------------------------------------
    1. placez-vous dans le dossier : /usr/share/centreon/tools
    2. puis exécuter le script suivant :

::

# php update_centreon_storage_logs.php

Exécution en mode non-interactif (>10 millions d’enregistrements)
-----------------------------------------------------------------
    1. placez-vous dans le dossier : /usr/share/centreon/tools
    2. puis exécuter le script suivant :

::

# nohup php update_centreon_storage_logs.php --password=root_password [--keep |--no-keep] > update_logs.logs &

.. note:: Options de démarrage:

  --password:
    mot de passe root de la base de données Centreon (ex. --password=my_root_password).
  --keep:
    indique que faut conserver les données de l’ancienne table centreon_storage.logs_old.
  --no-keep:
    indique que les données de l’ancienne table centreon_storage.logs_old peuvent être supprimées au fur et à mesure de la migration des données vers la nouvelle table centreon_storage.logs.
  --temporary-path:
    indique le dossier où seront stockés les fichiers temporaires.

.. warning::
  Si vous décidez de conserver les données de l'ancienne table centreon_storage.logs n'oubliez pas de vérifier l'espace disque disponible.
