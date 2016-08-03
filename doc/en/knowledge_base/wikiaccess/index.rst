================================
Configure the access to the wiki
================================

Before starting with *Centreon Knowledge Base*, you need to configure
it to access the wiki database. To do so you must fill a file called
*wiki.conf.php* which is at the root of your *Centreon Knowledge Base*
installation:
*<install_dir>/centreon/www/modules/centreon-knowledgebase/wiki.conf.php*.

The file looks like this::

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
| $db_name       | MediaWiki's database name                               |
+----------------+---------------------------------------------------------+
| $db_user       | MySQL user that can access MediaWiki's database         |
+----------------+---------------------------------------------------------+
| $db_password   | MySQL user's password                                   |
+----------------+---------------------------------------------------------+
| $db_host       | Host Ip or adress of the MediaWiki's database           |
+----------------+---------------------------------------------------------+
| $db_prefix     | MediaWiki's tables prefix                               |
+----------------+---------------------------------------------------------+
| $WikiURL       | Mediawiki's URL                                         |
+----------------+---------------------------------------------------------+
| $CentreonURL   | Your Centreon's URL                                     |
+----------------+---------------------------------------------------------+
| $etc_centreon  | Folder where Centreon's configurations files are stored |
+----------------+---------------------------------------------------------+
| $log_centreon  | Folder where Centreon's logs files are stored           |
+----------------+---------------------------------------------------------+

