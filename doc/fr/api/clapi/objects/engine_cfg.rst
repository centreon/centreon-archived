==========
Engine CFG
==========

Overview
--------

Object name: **ENGINECFG**

Show
----

In order to list available Engine conf, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a show 
  engine id;engine name;instance;engine comment
  1;Engine CFG Central;Central;Default Engine.cfg
  [...]

Columns are the following :

======= ===========================================
Order	Description
======= ===========================================
1	Engine ID

2	Engine configuration name

3	Instance that is linked to engine.cfg

4	Comments regarding the configuration file
======= ===========================================

Add
---

In order to add a Engine conf, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a add -v "Engine cfg for poller NY;Poller-NY;Just a small comment" 

Required fields are:

======== ===========================================
Order	 Description
======== ===========================================
1	 Nagios configuration name

2	 Instance that is linked to engine.cfg

3	 Comment regarding the configuration file
======== ===========================================

Del
---

If you want to remove a Engine conf, use the **DEL** action. The name is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a del -v "Engine cfg for poller NY" 


Setparam
--------

If you want to change a specific parameter of a Engine conf, use the **SETPARAM** action. The name is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a setparam -v "Engine cfg for poller NY;cfg_dir;/usr/local/engine/etc" 

Arguments are composed of the following columns:

======= =================================
Order	Column description
======= =================================
1	Name of Engine configuration

2	Parameter name

3	Parameter value
======= =================================

Parameters that you may change are:

================ =============================================================================================================================
Column	         Description
================ =============================================================================================================================
nagios_name	      Name

instance	 Instance that is linked to engine.cfg

broker_module	 example: [...] -v "Engine CFG 1;broker_module;/usr/lib64/nagios/cbmod.so /etc/centreon-broker/central-module.json",
                 you can use a | delimiter for defining multiple broker modules

nagios_activate	 *1* if activated, *0* otherwise

*	         Centreon CLAPI handles pretty much all the options available in a Engine configuration file.
                 Because the list is quite long, it is best to refer to the official documentation of Engine
================ =============================================================================================================================

Addbrokermodule
---------------

If you want to add new broker module without removing existing modules, use the **ADDBROKERMODULE**::

    [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a addbrokermodule -v "Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so"

Arguments are composed of the following columns:

======= =================================
Order	Column description
======= =================================
1	Name of Engine configuration

2	Module name
======= =================================

To add multiple modules in one line, it will put the separator "|" between the name of the modules
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a addbrokermodule -v "Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so|/etc/centreon-broker/central-module.json"

Delbrokermodule
---------------

If you want to delete broker module, use the **DELBROKERMODULE**::
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a delbrokermodule -v "Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so"


Arguments are composed of the following columns:

======= =================================
Order	Column description
======= =================================
1	Name of Engine configuration

2	Module name
======= =================================

To delete multiple modules in one line, it will put the separator "|" between the name of the modules
  [root@centreon ~]# ./centreon -u admin -p centreon -o ENGINECFG -a delbrokermodule -v "Engine cfg for poller NY;/usr/lib64/centreon-engine/externalcmd.so|/etc/centreon-broker/central-module.json"
