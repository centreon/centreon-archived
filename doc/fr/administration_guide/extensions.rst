==============
Les extensions
==============

***********
Les modules
***********

Les modules permettent d'ajouter des fonctionnalités supplémentaires à Centreon.
Il est possible d'installer des modules en utilisant l'utilitaire YUM ou à partir des fichiers sources (\*.tar.gz).

Les modules sont regroupés en 3 types :

* Les modules **communautaires**, sous licence GPL v2, développés par la communauté Centreon
* Les modules **core**, sous licence GPL v2, développés par l'équipe Centreon
* Les modules **propriétaires**, soumis à licence, développés par la société `Centreon <http://www.centreon.com>`_

Pour installer un module :

#. Installez le module à partir de la documentation associée (le plus souvent dans le répertoire **/usr/share/centreon/www/modules** sur le serveur Central)
#. Rendez-vous dans le menu **Administration > Extensions > Modules**

.. image :: /images/guide_exploitation/cextensions.png
   :align: center

Vous avez alors deux choix :

* Installer un module en cliquant sur le bouton |install|,
* Ou cliquez sur le bouton **Install/Upgrade all** pour installer tous les modules en même temps.

Le tableau ci-dessous résume les différentes colonnes de la page :

+-------------------------+------------------------------------------------------------------------------------------------------------+
|   Colonne               |  Description                                                                                               | 
+=========================+============================================================================================================+
| Nom                     | Contient le nom du module                                                                                  |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Nom réel                | Contient le nom complet du module                                                                          |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Informations            | Contient des informations à propos du module                                                               |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Version                 | Indique la version du module                                                                               |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Auteur                  | Indique l'auteur du module                                                                                 |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Date de fin de licence  | Indique la date d'expiration de la licence                                                                 |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Installé                | Indique si le module est installé ou non                                                                   |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Statut                  | Indique le statut du module : installé, installé mais sans licence, inconnu etc...                         |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Actions                 | Permet d'effectuer certaines actions sur un module :                                                       |
|                         |                                                                                                            |
|                         | Pour installer un module, cliquez sur l'icône : |install|                                                  |
|                         |                                                                                                            |
|                         | Pour configurer un module, cliquez sur l'icône |configure|                                                 |
|                         |                                                                                                            |
|                         | Pour supprimer un module, cliquez sur l'icône |delete|, puis confirmez la suppression                      |
|                         |                                                                                                            |
|                         | Pour mettre à jour un module, cliquer sur l'icône |update| puis suivre le processus                        |
+-------------------------+------------------------------------------------------------------------------------------------------------+

***********
Les widgets
***********

Les widgets permettent de construire de véritables vues personnalisées, abordées au chapitre :ref:`les vues personnalisées <leswidgets>`.

Pour installer un widget :

#. Installez le widget à partir de la documentation associée (le plus souvent dans le répertoire **/usr/share/centreon/www/widgets** sur le serveur Central)
#. Rendez-vous dans le menu **Administration > Extensions > Widgets**

.. image :: /images/guide_exploitation/cwidgets.png
   :align: center

Vous avez alors deux choix :

* Installer un widget en cliquant sur le bouton |install|,
* Ou cliquez sur le bouton **Install/Upgrade all** pour installer tous les widgets en même temps.

Le tableau ci-dessous résume les différentes colonnes de la page :

+-------------------------+------------------------------------------------------------------------------------------------------------+
|   Colonne               |  Description                                                                                               | 
+=========================+============================================================================================================+
| Titre                   | Contient le nom du widget                                                                                  |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Description             | Contient des informations à propos du widget                                                               |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Version                 | Indique la version du widget                                                                               |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Auteur                  | Indique l'auteur du widget                                                                                 |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Actions                 | Permet d'effectuer certaines actions sur un widget :                                                       |
|                         |                                                                                                            |
|                         | Pour installer un widget, cliquez sur l'icône |install|                                                    |
|                         |                                                                                                            |
|                         | Pour supprimer un widget, cliquez sur l'icône |delete|, puis confirmez la suppression                      |
|                         |                                                                                                            |
|                         | Pour mettre à jour un widget, cliquer sur l'icône |update| puis suivre le processus                        |
+-------------------------+------------------------------------------------------------------------------------------------------------+

.. |install|    image:: /images/guide_exploitation/generate_conf.png
.. |delete|    image:: /images/guide_exploitation/delete.png
.. |configure|    image:: /images/guide_exploitation/wrench.gif
.. |update|    image:: /images/guide_exploitation/upgrade.png
