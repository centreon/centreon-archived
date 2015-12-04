.. _upgrade_from_packages:

===============
Mise à jour RPM
===============

Pour mettre à jour votre interface Centreon, il suffit d'entrer la commande suivante

 ::

 yum update centreon

Si tout se passe correctement, loguez vous sur l'interface Centreon et suivez les différentes étapes.

***************
Mise à jour Web
***************

Présentation
------------

.. image:: /_static/images/upgrade/step01.png
   :align: center

Contrôle des dépendances
------------------------

Cette étape contrôle la liste des dépendances PHP.

.. image:: /_static/images/upgrade/step02.png
   :align: center

Notes de version
----------------

.. image:: /_static/images/upgrade/step03.png
   :align: center

Mise à jour des bases de données
--------------------------------

Cette étape met à jour le modèle des bases de données ainsi que les données, version par version.

.. image:: /_static/images/upgrade/step04.png
   :align: center

Finalisation
------------

.. image:: /_static/images/upgrade/step05.png
   :align: center
