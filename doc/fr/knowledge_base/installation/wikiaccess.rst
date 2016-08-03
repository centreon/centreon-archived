================================
Configuration de l'accès au wiki
================================

Afin d'utiliser *Centreon Knowledge Base*, vous devez le configurer pour qu'il accède à la base de données du wiki.
Pour cela vous devez renseigner le fichier *wiki.conf.php* qui se situe à la racine du répertoire d'installation de *Centreon Knowledge Base*:
*<install_dir>/centreon/www/modules/centreon-knowledgebase/wiki.conf.php*.

Le fichier ressemble à ceci::


  /*
   * MySQL Database Connexion
   */
  $db_name = "wikidb";
  $db_user = "centreon";
  $db_password = "password";
  $db_host = "localhost";
  $db_prefix = "";

  /*
   * Wiki URL without a / at the end
   */
  $WikiURL = "http://wiki.localhost/mediawiki";
  $CentreonURL = "http://localhost/centreon";
  $etc_centreon = "/etc/centreon/";
  $log_centreon = "/var/log/centreon/";

+----------------+---------------------------------------------------------+
| Conf variables | Description                                             |
+================+=========================================================+
| $db_name       | Nom de la base de données MySQL MediaWiki               |
+----------------+---------------------------------------------------------+
| $db_user       | Utilisateur MySQL de la base de données MediaWiki       |
+----------------+---------------------------------------------------------+
| $db_password   | Mot de passe utilisateur MySQL MediaWiki                |
+----------------+---------------------------------------------------------+
| $db_host       | Hôte ou adresse IP du serveur MySQL                     |
+----------------+---------------------------------------------------------+
| $db_prefix     | Préfixe des tables Mediawiki                            |
+----------------+---------------------------------------------------------+
| $WikiURL       | URL de Mediawiki                                        |
+----------------+---------------------------------------------------------+
| $CentreonURL   | URL de Centreon                                         |
+----------------+---------------------------------------------------------+
| $etc_centreon  | Répertoire des fichiers de configuration de Centreon    |
+----------------+---------------------------------------------------------+
| $log_centreon  | Répertoire des fichiers de log de Centreon              |
+----------------+---------------------------------------------------------+

