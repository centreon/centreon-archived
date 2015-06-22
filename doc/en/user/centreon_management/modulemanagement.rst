Module management
#################

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

   external/bin/centreonConsole core:module:Manage:upgrade --module centreon-performance
   
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
   
   external/bin/centreonConsole core:module:Manage:uninstall --module myModuleName

If you execute this command:

::

   external/bin/centreonConsole core:module:Manage:uninstall --module centreon-performance

You have this this response: 
::

   Starting removal of Centreon Performance module
   Checking operation validity...     Done
   Removal of Centreon Performance module complete


Deploy statics file
^^^^^^^^^^^^^^^^^^^^
This command move the static files (LESS, CSS and JS) in the directory adequate.
::

   external/bin/centreonConsole core:module:Manage:deployStatic --module myModuleName

Deploy forms
^^^^^^^^^^^^
This command regenerates the forms contained in this module. The possible values are:

::

   external/bin/centreonConsole core:module:Manage:deployForms --module myModuleName
