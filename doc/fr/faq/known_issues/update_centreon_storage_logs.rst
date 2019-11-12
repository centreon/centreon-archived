.. _update_centreon_storage_logs:

=============================================
Mise à jour de la table centreon_storage.logs
=============================================

Problématique
=============

Certains clients ont atteint le nombre maximal d'enregistrements dans la table centreon_storage.logs qui actuellement
ne peuxtcontenir que 2 147 483 647 enregistrements (entier signé, comptage à partir de 0).

Broker ne peut donc plus rajouter d'élément dans cette table.

Objectif
========

L’objectif de cette procédure est de permettre la modification de la colonne log_id de la table centreon_storage.logs

La façon la plus simple pour effectuer cette modification consisterait à exécuter la commande suivante directement sur
la table afin de modifier le type de la colonne log_id : ::

    ALTER TABLE centreon_storage.logs MODIFY log_id BIGINT(20) NOT NULL AUTO_INCREMENT

Cependant cette opération bloquerait la table durant la modification et pourrait prendre plusieurs heures avant la fin
de l'opération. Broker se verrait contraint de faire de la rétention et bloquerait la remontée de log sur l'interface
Centreon durant tout le processus.

Malgré tout cette option pourrait être envisagée dans le cas où la table ne contiendrait que peu d'enregistrements
(< 10 millions).

Pour les grosses volumétries nous avons réalisé un script permettant la migration des données par partition de
l'ancienne table vers la nouvelle sans interruption de service.

Prérequis
=========

Mot de passe
------------

Avant de pouvoir exécuter le script il est nécessaire de vous munir du mot de passe root de la base de données
Centreon.

Activation de PHP pour Centreon 19.10 (**Centos7**)
---------------------------------------------------

Sur la nouvelle version 19.10 de Centreon (Centos7) il est nécessaire d’activer PHP avant de pouvoir lancer des scripts
PHP en ligne de commande.

La commande est la suivante : ::

    # scl enable rh-php72 bash

Explications
============

Diagramme fonctionnel :

.. image:: /images/faq/workflow_centreon_storage_logs.png
    :align: center

La mise à jour de la table se déroulera de la façon suivante:

1. renommage de la table **centreon_storage.logs** en **centreon_storage.logs_old**
2. création de la nouvelle table **centreon_storage.logs**
3. migration des données par partition

Mise à jour
===========

Sur une installation classique de Centreon, le script se situe à l’emplacement suivant : ::

    # usr/share/centreon/tools/update_centreon_storage_logs.php

Exécution en mode interactif (<10 millions d’enregistrements)
-------------------------------------------------------------

1. placez-vous dans le dossier : /usr/share/centreon/tools
2. puis exécuter le script suivant : ::

    # php update_centreon_storage_logs.php

Exécution en mode non-interactif (>10 millions d’enregistrements)
-----------------------------------------------------------------

1. placez-vous dans le dossier : /usr/share/centreon/tools
2. puis exécuter le script suivant : ::

    # nohup php update_centreon_storage_logs.php --password=root_password [--keep |--no-keep] > update_logs.logs &

.. note:: Options de démarrage :
    
    --password:
        mot de passe root de la base de données Centreon (ex. --password=my_root_password).
    --keep:
        indique que faut conserver les données de l’ancienne table (renommé en centreon_storage.logs_old).
    --no-keep:
        indique que les données de l’ancienne table centreon_storage.logs_old peuvent être supprimées au fur et à mesure de
        la migration des données vers la nouvelle table centreon_storage.logs.
    --temporary-path:
        indique le dossier où seront stockés les fichiers temporaires.

.. warning::
    Si vous décidez de conserver les données de l'ancienne table centreon_storage.logs n'oubliez pas de vérifier l'espace
    disque disponible.

Reprise de la migration
-----------------------

Si, pour une raison quelconque, vous souhaitez arrêter le script de migration sachez qu’il est possible de le redémarrer
afin qu’il reprenne là où il en était.

.. note:: Option de reprise :
    
    --continue:
        Cette option permet de spécifier la reprise des migrations après une interruption d'exécution.
        
        Si cette option est spécifiée les structures des tables *centreon_storage.logs* et *centreon_storage.logs_old*
        ne seront pas touchées.

Pour cela il y a deux possibilités :

1. En spécifiant le nom de dernière partition traitée.
2. Sans spécifier le nom de la dernière partition traitée, le script utilisera la première partition non-vide de la
  table centreon_storage.logs_old.

.. warning::
    L’utilisation de l’option *--continue* sans spécifier le nom de la dernière partition traitée n’est à utiliser que
    si vous aviez spécifié l’option *--no-keep* lors de la précédente exécution du script.

Exemples : ::

    # nohup php update_centreon_storage_logs.php --continue [--password=root_password]

ou ::

    # nohup php update_centreon_storage_logs.php --continue=last_partition_name [--password=root_password]

.. note::
    Pour connaître le nom de la dernière partition traitée il vous suffit de regarder dans les logs de traitement du
    script le nom de la dernière partition en cours de traitement avant l’arrêt du script.