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

  # cd /usr/share/centreon/bin
  # ./centreon -u admin -p centreon [...]

Obviously, the **-u** option is for the username and the **-p** option is for the password.
The password can be in clear or the encrypted in the database.

.. note::
    If your passwords are encoded with SHA1 in database (MD5 by default), use the **-s** option::

  # ./centreon -u admin -p centreon -s [...]
