============
CENGINE CFG
============

Overview
--------

Object name: **ENGINECFG**

Show
----

In order to list available Centreon Engine conf, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a show 
  id;name;instance;comment
  1;Centreon Engine CFG 1;Central;Default CentreonEngine.cfg
  [...]

Columns are the following :

======= ===============================================
Order	Description
======= ===============================================
1	Centreon Engine ID

2	Centreon Engine configuration name

3	Instance that is linked to centreon-engine.cfg

4	Comments regarding the configuration file
======= ===============================================

Add
---

In order to add a Centreon Engine conf, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a add -v "Centreon Engine cfg for poller NY;Poller-NY;Just a small comment" 

Required fields are:

======== ================================================
Order	 Description
======== ================================================
1	 Centreon Engine configuration name

2	 Instance that is linked to centreon-engine.cfg

3	 Comment regarding the configuration file
======== ================================================


Del
---

If you want to remove a Centreon Engine conf, use the **DEL** action. The name is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a del -v "Centreon Engine cfg for poller NY" 


Setparam
--------

If you want to change a specific parameter of a Centreon Engine conf, use the **SETPARAM** action. The name is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a setparam -v "Centreon Engine cfg for poller NY;cfg_dir;/usr/local/nagios/etc" 

Arguments are composed of the following columns:

======= =====================================
Order	Column description
======= =====================================
1	Name of Centreon Engine configuration

2	Parameter name

3	Parameter value
======= =====================================

Parameters that you may change are:

================ =============================================================================================================================
Column	         Description
================ =============================================================================================================================
nagios_name	         Name

instance	 Instance that is linked to centreon-engine.cfg

broker_module	 example: [...] -v "Engine CFG NY;broker_module;/usr/lib64/nagios/cbmod.so /etc/centreon-broker/central-module.json",
                 you can use a | delimiter for defining multiple broker modules

nagios_activate	 *1* if activated, *0* otherwise

*	         Centreon CLAPI handles pretty much all the options available in a centreon-engine configuration file.
                 Because the list is quite long, it is best to refer to the official documentation of Centreon Engine
================ =============================================================================================================================


Addbrokermodule
---------------

If you want to add new broker module without removing existing modules, use the **ADDBROKERMODULE**::
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a addbrokermodule -v "Centreon Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so"


Arguments are composed of the following columns:

======= =====================================
Order	Column description
======= =====================================
1	Name of Centreon Engine configuration

2	Module name
======= =====================================

To add multiple modules in one line, it will put the separator "|" between the name of the modules::
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a addbrokermodule -v "Centreon Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so|/etc/centreon-broker/central-module.json"


Delbrokermodule
---------------

If you want to delete broker module, use the **DELBROKERMODULE**::
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a delbrokermodule -v "Centreon Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so"


Arguments are composed of the following columns:

======= =====================================
Order	Column description
======= =====================================
1	Name of Centreon Engine configuration

2	Module name
======= =====================================

To delete multiple modules in one line, it will put the separator "|" between the name of the modules::
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a delbrokermodule -v "Centreon Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so|/etc/centreon-broker/central-module.json"
