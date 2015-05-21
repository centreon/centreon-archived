Centreon Management
###################

Multiple commands are availbale to manage your centreon, add/upgrade/uninstall modules. 

First, you must navigate to the folder /srv/centreon. 


Help command
============

To see all the available commands

::

   external/bin/centreonConsole -l core


Installation command
====================

To instal your centreon, you would like to execute this commande.
::

   external/bin/centreonConsole core:Internal:install

This procedure will detect if you have an old version of centreon, if this is the case, the console will display the message "Starting to migrate to Centreon 3.0". This is the same procedure as migrating an older version to new version of centreon will require referere to this section.
If this is a first installation, the console will display the message "Starting to install Centreon 3.0",the procedure will install the basic modules, centreon-main, centreon-security, centreon-administration, centreon-configuration, centreon-realtime, centreon-customview and initialize the database used by centreon which is CENTREON.


Upgrade
^^^^^^^

This procedure update the database and all the basic modules centreon-main, centreon-security, centreon-administration, centreon-configuration, centreon-realtime, centreon-customview.

::

   external/bin/centreonConsole core:Internal:upgrade

Migrate
^^^^^^^
This command upgrade database and the basic modules centreon-main, centreon-security, centreon-administration, centreon-configuration, centreon-realtime, centreon-customview.
::

   core:Internal:migrate
   
Uninstall
^^^^^^^^^
This command will remove the core modules and the database. The command is not yet developped, 

::

   core:Internal:uninstall


Database management
===================


Generate database
-----------------

::

   core:database:Configuration:generate


Tools for database
------------------

Convert sql datas to JSON
^^^^^^^^^^^^^^^^^^^^^^^^^
::

   core:database:Tools:sqlToJson


Convert JSON datas to sql
^^^^^^^^^^^^^^^^^^^^^^^^^
::

   core:database:Tools:jsonToSql


Migrate class
^^^^^^^^^^^^^
::

   core:database:Tools:generateMigrationClass



Module informations
===================

Simple list
^^^^^^^^^^^

::

   core:module:Infos:simpleList

Extends list
^^^^^^^^^^^^
::

   core:module:Infos:extendedList


Show
^^^^
::

   core:module:Infos:show


Module management
=================

Install
^^^^^^^
::
   
   core:module:Manage:install

Upgrade
^^^^^^^

::

   core:module:Manage:upgrade

Uninstall
^^^^^^^^^

::
   
   core:module:Manage:uninstall

Deploay statics file
^^^^^^^^^^^^^^^^^^^^
::

   core:module:Manage:deployStatic

Deploy forms
^^^^^^^^^^^^
::

   core:module:Manage:deployForms

Genrate
=======

::

   core:module:Tools:generate

