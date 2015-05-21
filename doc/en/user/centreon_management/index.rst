Centreon Management
###################

Multiple commands are availbale to manage your centreon, add/upgrade/uninstall modules. 

First, you must navigate to the folder /srv/centreon. 


Help command
============

To see all the available commands

::

   external/bin/centreonConsole -l core


Return
::
  [core]
    core:Internal:install
    core:Internal:upgrade
    core:Internal:migrate
    core:Internal:uninstall

    core:database:Configuration:generate

    core:database:Tools:sqlToJson
    core:database:Tools:jsonToSql
    core:database:Tools:generateMigrationClass

    core:module:Infos:simpleList
    core:module:Infos:extendedList
    core:module:Infos:show

    core:module:Manage:install
    core:module:Manage:upgrade
    core:module:Manage:uninstall
    core:module:Manage:deployStatic
    core:module:Manage:deployForms

    core:module:Tools:generate


Installation command
====================

To instal your centreon, you would like to execute this commande.
::

   external/bin/centreonConsole core:Internal:install

This procedure will detect if you have an old version of centreon, if this is the case, the console will display the message : 
::

   Starting to migrate to Centreon 3.0
   Preparing Migration... Done
   Migrating centreon database... Done
   Installing centreon-main module... Starting installation of Centreon Main module
   Checking operation validity...     Done
   [Error 0] => Module already installed


This is the same procedure as migrating an older version to new version of centreon will require referere to this section.
If this is a first installation, the console will display the message :
::

   Starting to install Centreon 3.0
   Creating centreon database... Done
   Starting installation of Centreon Main module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...Installation of validators...     Done
     Done
   Installation of Centreon Main module complete
   Starting installation of Centreon Administration module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...     Done
   Installation of Centreon Administration module complete
   Starting installation of Centreon Custom Views module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...     Done
   Installation of Centreon Custom Views module complete
   Starting installation of Centreon Realtime module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...     Done
   Installation of Centreon Realtime module complete
   Starting installation of Centreon Configuration module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...Installation of validators...     Done
         Done
   Installation of Centreon Configuration module complete
   Starting installation of Centreon Security module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...     Done
   Installation of Centreon Security module complete
   Centreon 3.0 has been successfully installed




The procedure will initialize the database used by centreon which is CENTREON and install the basic modules:

======================= ====================
Module                  Description         
======================= ====================
centreon-main           Centreon Main
centreon-security       Centreon Security
centreon-administration Centreon Administration
centreon-configuration  Centreon Configuration
centreon-realtime       Centreon Realtime
centreon-customview     Centreon Customview
======================= ====================


Upgrade
^^^^^^^

This procedure update the database and all the basic modules:

======================= ====================
Module                  Description         
======================= ====================
centreon-main           Centreon Main
centreon-security       Centreon Security
centreon-administration Centreon Administration
centreon-configuration  Centreon Configuration
centreon-realtime       Centreon Realtime
centreon-customview     Centreon Customview
======================= ====================

::

   external/bin/centreonConsole core:Internal:upgrade

Migrate
^^^^^^^
This command upgrade database and the basic modules :

======================= ====================
Module                  Description         
======================= ====================
centreon-main           Centreon Main
centreon-security       Centreon Security
centreon-administration Centreon Administration
centreon-configuration  Centreon Configuration
centreon-realtime       Centreon Realtime
centreon-customview     Centreon Customview
======================= ====================

::

   external/bin/centreonConsole core:Internal:migrate

Example of return
::

   Starting to migrate to Centreon 3.0
   Preparing Migration... Done
   Migrating centreon database... Done
   Installing centreon-main module... Starting installation of Centreon Main module
   Checking operation validity...     Done
   [Error 0] => Module already installed


Uninstall
^^^^^^^^^
This command will remove the core modules and the database. The command is not yet developped.

::

   external/bin/centreonConsole core:Internal:uninstall


Database management
===================


Generate database
-----------------
This procedure to update the schematic of the database , it will compare the current version with the new schematic generated XML files.

::

   external/bin/centreonConsole core:database:Configuration:generate


Tools for database
------------------

Convert sql datas to JSON
^^^^^^^^^^^^^^^^^^^^^^^^^
This command will generate a JSON file from a table in the database . You must replace the argument MyTableName by the name of the table you are interested in.

========= ==================== =======
argument  description          example
========= ==================== =======
dbname    The name of database db_centreon
tablename The name of table    cfg_tags
========= ==================== =======

::

   external/bin/centreonConsole core:database:Tools:sqlToJson dbname=db_centreon:tablename=myTableName

Example of return:
::

   [{"tag_id":"28","tagname":"europe.paris.defense"},{"tag_id":"4","tagname":"PARIS"},{"tag_id":"3","tagname":"REIMS"},{"tag_id":"11","tagname":"taghost"},{"tag_id":"2","tagname":"TOULOUSEMIRAIL"}]


Convert JSON datas to sql
^^^^^^^^^^^^^^^^^^^^^^^^^
This command will generate a SQL code from a JSON file. The file argument will contain the source file. The second argument contains the name of the table in the database . The latter is optional. It is useful if you want to send the answer to a file, if empty the contents will be displayed in the concole.

=========== ================================== ==============
argument    description                        example
=========== ================================== ==============
file        The file to import                 /tmp/tags.json
tablename   The name of table                  cfg_tags
destination The file where data will be stored /tmp/tags.sql
=========== ================================== ==============

::

   external/bin/centreonConsole core:database:Tools:jsonToSql file=mySource,tablename=myTableName,destination=myDestination


Migrate class
^^^^^^^^^^^^^
This command will generate a database migration file. it will not execute, it may be overloaded with new post orders or pre-order.

::

   external/bin/centreonConsole core:database:Tools:generateMigrationClass



Module informations
===================

Simple list
^^^^^^^^^^^
This command will display all active modules.

::

   external/bin/centreonConsole core:module:Infos:simpleList

Return

::

   centreon-administration
   centreon-configuration
   centreon-customview
   centreon-main
   centreon-realtime
   centreon-security

Extends list
^^^^^^^^^^^^
This command will display the details of active modules: version, name, alias, description, author of module.

::

   external/bin/centreonConsole core:module:Infos:extendedList


Return

::

   name;alias;description;version;author;isactivated;isinstalled
   centreon-administration;Centreon Administration;Centreon Administration Module;2.99.2;Centreon;2;2
   centreon-configuration;Centreon Configuration;Centreon Configuration Module;2.99.2;Centreon;1;2
   centreon-customview;Centreon Custom Views;Centreon Custom Views Module;2.99.2;Centreon;1;2
   centreon-main;Centreon Main;Centreon Main Module;2.99.2;Centreon;2;2
   centreon-realtime;Centreon Realtime;Realtime module for Centreon;2.99.2;Centreon;1;2
   centreon-security;Centreon Security;Centreon Security Module;2.99.2;Centreon;2;2


Show
^^^^

The command is not yet developped.
::

   external/bin/centreonConsole core:module:Infos:show moduleName=myModuleName


Module management
=================

Install
^^^^^^^
This command will install the module given in argument. It update the database and forms and validators. It will move the static files (LESS, CSS and JS) in the directory adequate and install menu and hooks.
::
   
   external/bin/centreonConsole core:module:Manage:install module=myModuleName

Example

If you execute this command:
::

   external/bin/centreonConsole core:module:Manage:install module=centreon-performance
   
You have this this response: 
::

  Starting installation of Centreon Performance module
  Checking operation validity...     Done
  Updating centreon database... Done
  Deployment of Forms...     Done
  Installation of Centreon Performance module complete


Upgrade
^^^^^^^
This command will upgrade the module given in argument. It update the database and forms and validators. It will move the static files (LESS, CSS and JS) in the directory adequate and install menu and hooks.
::

   external/bin/centreonConsole core:module:Manage:upgrade module=myModuleName

Example

If you execute this command:

::

   external/bin/centreonConsole core:module:Manage:upgrade module=centreon-performance
   
You have this this response: 
::

   Starting upgrade of Centreon Performance module
   Checking operation validity...     Done
   Updating centreon database... Done
   Deployment of Forms...     Done
   Upgrade of Centreon Performance module complete



Uninstall
^^^^^^^^^
This command will remove the module given in argument, it will remove its forms.
::
   
   external/bin/centreonConsole core:module:Manage:uninstall module=myModuleName

If you execute this command:

::

   external/bin/centreonConsole core:module:Manage:uninstall module=centreon-performance

You have this this response: 
::

   Starting removal of Centreon Performance module
   Checking operation validity...     Done
   Removal of Centreon Performance module complete


Deploy statics file
^^^^^^^^^^^^^^^^^^^^
This command move the static files (LESS, CSS and JS) in the directory adequate.
::

   external/bin/centreonConsole core:module:Manage:deployStatic module=myModuleName

Deploy forms
^^^^^^^^^^^^
This command regenerates the forms contained in this module. The possible values are:

::

   external/bin/centreonConsole core:module:Manage:deployForms module=myModuleName
