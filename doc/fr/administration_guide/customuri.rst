=================
URI personnalisée
=================

Il est possible de modifier l'URI de Centreon. Par exemple, **/centreon** peut être remplacé par **/monitoring**.

Pour mettre à jour l'URI Centreon, vous devez suivre les étapes suivantes:

* Supprimer ce dossier sur le serveur central : **centreon/www/static**
* Remplacer les occurences **/centreon** par **/votre_uri_personnalise** dans **centreon/www/index.html**
* Remplacer les occurences **/centreon** par **/votre_uri_personnalise** dans **centreon/www/.htaccess**
* Naviguer vers l'URL Centreon
