##############
Centreon 2.8.6
##############

Bug Fixes
=========

KB
--

* Fix wiki links of objects with spaces in their name

If you already used a knowledge base, please execute following script :
::

	php /usr/share/centreon/bin/migrateWikiPages.php


Known bugs or issues
====================

* There's an issue in the ldap configuration form. A fix is available and will be package with the next bugfix version. Until then you can apply the patch available `here <https://github.com/centreon/centreon/commit/8aef6dfa4e3af27f16277b4211655889cf91fb71>`_
* There's an issue on all listing pages. A fix is available and will be package with the next bugfix version. Until then you can apply the `available patch <https://github.com/centreon/centreon/commit/d9b58f203f1af377575328d6f955ac1e9c8fb804>`_
