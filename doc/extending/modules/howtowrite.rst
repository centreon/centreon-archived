=====================
How to write a module
=====================

You want to create a new module for Centreon 2 or to adapt an existing
one? You're at the right place!

You should know Centreon contains a page dedicated to the installation
and the uninstallation of modules (*Administration > Modules*). To
make the module appears on this page, its directory must be placed
inside Cetreon's ``modules/`` directory. Example::

  /usr/local/centreon/www/modules/module-Dummy

An empty module template can be found inside `Centreon's repository
<http://svn.centreon.com/trunk/module-Dummy>`_.

*****
Basis
*****

The essential elements your module's directory must contain are presented below (\* = required):

**[conf.php]\***::
  
  // Short module's name. Must be equal to your module's directory name
  $module_conf['dummy']['name'] = "dummy"; 
  // Full module's name
  $module_conf['dummy']['rname'] = "Dummy Module";
  // Module's version
  $module_conf['dummy']['mod_release'] = "2.0"; 
  // Additional information
  $module_conf['dummy']['infos'] = "First of all"; 
  // Allow your module to be uninstalled
  $module_conf['dummy']['is_removeable'] = "1"; 
  // Module author's name
  $module_conf['dummy']['author'] = "Centreon Team"; 
  // 1: the module executes an SQL file for installation and/or uninstallation
  // 0: the module doesn't execute any SQL file
  $module_conf['dummy']['sql_files'] = "1"; 
  // 1: the module executes a PHP file for installation and/or uninstallation
  // 0: the module doesn't execute any SQL file
  $module_conf['dummy']['php_files'] = "1"; 

**[infos > infos.txt]**

This file can contain various information about your module.

**[php > install.php]**

This PHP file is executed at module installation if it is configured
inside the *conf.php* file.

**[php > uninstall.php]**

This PHP file is executed at module uninstallation if it is configured
inside the *conf.php* file.

**[sql > install.sql]**

This SQL file is executed during the module installation if it is
configured inside the *conf.php* file. If you want your module to be
available from Centreon menus, you must insert new entries into the
``topology`` table of the ``centreon`` database. An example is
available inside the ``Dummy`` module.

**[sql > uninstall.sql]**

This SQL file is executed during the module uninstallation if it is
configured inside the *conf.php* file. It can also remove your module
from Centreon menus.

**[generate_files > \*.php]**

The PHP files contained inside the ``generate_files`` directory will
be executed during the Nagios configuration files generation (inside
*Configuration > Nagios*). Those files must generate Nagios
configuration files.

**[UPGRADE > dummy-x.x > sql > upgrade.sql]**

Centreon provides an upgrade system for modules. To use it, just add a
directory under ``UPGRADE`` named using the following pattern:
``<module name>-<version>``. When clicking on the upgrade button,
Centreon will search for scripts to execute, following the logical
order of versions.

For example, if the version 1.0 of the dummy module is installed and
the following directories exist::

  $ ls UPGRADE
  dummy-1.1 dummy-1.2

Centreon will execute the scripts in the following order : 1.1, 1.2. A
configuration file in each upgrade directory is present in order to
allow (or not) the execution.

You're free to organize the remaining files (your module's content) as
you like.

********
Advanced
********

That's great, you know how to install a module! As an empty module is
not really usefull, put your imagination at work. Knowing that you can
do almost everything, it should not be too complicated :-).

Connecting to the database
==========================

You can use the ``centreon``, ``centstorage`` and ``ndo`` databases by
calling the following file: ``centreon/www/class/centreonDB.class.php``.

For example, execute requests like this:

.. sourcecode:: php

   <?
   $pearDB = new CentreonDB();
   $pearDB->query("SELECT * FROM host");
   ?>

Existing functions
==================

You can access most of the functions already developed within Centreon
using ``include()`` statements. They're generally stored in
``centreon/www/class/``.

Before developing your own function, check the existing code, it could
spare your time!

