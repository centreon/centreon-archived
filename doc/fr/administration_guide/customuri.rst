=================
URI personnalisée
=================

Il est possible de modifier l'URI de Centreon. Par exemple, **/centreon** peut être remplacé par **/monitoring**.

Pour mettre à jour l'URI Centreon, vous devez suivre les étapes suivantes:

1. Rendez-vous dans le menu **Administration > Parameters > Centreon UI** et modifier le champ **Centreon Web Directory**

.. image:: /_static/images/adminstration/custom_uri.png
    :align: center

2. Sur le serveur Centreon :

* Supprimez le répertoire **centreon/www/static**.
* Remplacez les occurences **/centreon** par **/votre_uri_personnalise** dans **centreon/www/index.html**
* Remplacez les occurences **/centreon** par **/votre_uri_personnalise** dans **centreon/www/.htaccess**
* Naviguez vers l'URL Centreon
