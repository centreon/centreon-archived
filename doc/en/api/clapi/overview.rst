========
Overview
========

Centreon CLAPI aims to offer (almost) all the features that are available on the user interface in terms of configuration.

Features
--------

 - Add/Delete/Update objects such as hosts, services, host templates, host groups, contacts etc...
 - Generate configuration files
 - Test configuration files
 - Move configuration files to monitoring pollers
 - Restart monitoring pollers
 - Import and export objects


Basic usage
-----------

All actions in Centreon CLAPI will require authentication, so your commands will always start like this::

  # cd /usr/share/centreon/www/modules/centreon-clapi/core
  # ./centreon -u admin -p centreon [...]

Obviously, the **-u** option is for the username and the **-p** option is for the password. If your passwords 
are encoded with SHA1 in database, use the **-s** option::

  # ./centreon -u admin -p centreon -s [...]
