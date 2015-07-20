Installation command
####################


Install
^^^^^^^

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



The procedure will initialize the database used by centreon which is CENTREON and install the core modules:

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

This procedure update the database and all the core modules:

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
This command upgrade database and the core modules :

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


Uninstall (Not yet implemented)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
This command will remove the core modules and the database.

::

   external/bin/centreonConsole core:Internal:uninstall
