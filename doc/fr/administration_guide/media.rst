==========
Les médias
==========

Les médias sont des images utilisées par l'interface web de Centreon.
Ces images sont utilisées afin de pouvoir représenter de manière plus intuitive les ressources supervisées, proposer des fonds de cartes pour des modules de cartographie, ...

******************
Gestion des images
******************

L'ensemble des images est stocké dans le dossier **/usr/share/centreon/www/img/media** du serveur Centreon.

Il existe deux méthodes pour ajouter des images dans ce dossier :

* Le faire manuellement
* Utiliser la synchronisation automatique : cette méthode a l'avantage de pouvoir importer plusieurs images à la fois.

Ajout manuel
============

Pour ajouter une image dans Centreon :

#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **Images**
#. Cliquez sur **Ajouter**

La fenêtre suivante s'affiche :

.. image :: /images/guide_exploitation/dmedias.png
   :align: center

* Le champ **Nouveau dossier ou dossier existant** permet d'ajouter un nouveau dossier au sein du dossier d'images ou de choisir un dossier déjà existant dans lequel pourra être copié l'image
* Le champ **Image ou archive** permet de sélectionner l'image ou l'ensemble d'images contenu dans une archive qui sera importée
* Le champ **Commentaires** permet de décrire l'image

Synchroniser les images
=======================

Pour synchroniser une ou plusieurs images dans les médias Centreon :

#. Copiez vos images au sein du dossier d'image (les images peuvent être situées dans des dossiers)
#. Assurez-vous que l'utilisateur qui exécute votre serveur web a les droits en lecture sur ces images
#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **Médias**
#. Cliquez sur **Synchroniser le répertoire des images**

La fenêtre suivante importe les nouvelles images :

.. image :: /images/guide_exploitation/dmediasimports.png
   :align: center
