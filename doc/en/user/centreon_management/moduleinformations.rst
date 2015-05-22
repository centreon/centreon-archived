Module informations
###################


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


Show (Not yet implemented)
^^^^^^^^^^^^^^^^^^^^^^^^^^

::

   external/bin/centreonConsole core:module:Infos:show moduleName=myModuleName
