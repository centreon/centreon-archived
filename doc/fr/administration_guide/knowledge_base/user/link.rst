.. _wiki-page-link:

Lien avec l'interface web de supervision
========================================

La base de connaissance est accessible depuis l'interface Centreon Web 
à travers un icône cliquable (voir ci dessous)

.. image:: ../../../_static/images/knowledge/screen_host_monitoring.png
   :align: center

En cliquant sur l'icône, l'utilisateur est redirigé vers la base de connaissance 
correspondante. 

Si l'icône est situé à gauche du nom de l'hôte, la base de connaissance
concernera l'hôte. 

Si l'icône est situé à droite du nom du service, la base de connaissance 
concernera le service.

Synchronisation
---------------

Un cron se charge de réaliser les mises à jour de la configuration des hôtes, des services 
ainsi que des modèles.

Par exemple, si vous créez une page dans le wiki en utilisant le motif habituel 
(ex : ``Host:Centreon-Server`` ou ``Service:Centreon-Server Disk-/``), le cron ajoutera automatiquement 
le lien vers la page du wiki correspondante dans le champ **URL** de la table **Informations étendues**.
