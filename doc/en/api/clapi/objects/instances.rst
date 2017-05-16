===================
Instances (Pollers)
===================

Overview
--------

Object name: **INSTANCE**

Show
----

In order to list available instances, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o INSTANCE -a show 
  id;name;localhost;ip address;activate;status;init script;monitoring engine;bin;stats bin;perfdata;ssh port
  1;Central;1;127.0.0.1;1;0;/etc/init.d/nagios;NAGIOS;/usr/local/nagios/bin/nagios;/usr/local/nagios/bin/nagiostats;/usr/local/nagios/var/service-perfdata;22
  [...]


Columns are the following:

================= ================================================================
Column	          Description
================= ================================================================
ID	          ID

Name	          Name

Localhost	  *1* if it is the main poller, *0* otherwise

IP Address	  IP address of the poller

Activate	  *1* if poller is enabled, *0* otherwise

Status	          *1* if poller is running, *0* otherwise

Init script	  Init script path

Monitoring Engine Engine used on poller: *NAGIOS*, *ICINGA*, *SHINKEN*...

Bin	          Path of the Scheduler binary

Stats Bin	  Path of the Nagios Stats binary

Perfdata	  Path of perfdata file

SSH Port	  SSH Port
================= ================================================================


Add
---

In order to add an instance you use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o INSTANCE -a add -v "Poller test;10.30.2.55;22;NAGIOS" 

Required fields are:

=================== =====================================================
Column	            Description
=================== =====================================================
Name	
Address	            IP address of the poller

SSH Port	    SSH port

Monitoring Engine   Engine used on poller: *NAGIOS*, *ICINGA*, *SHINKEN*
=================== =====================================================


Del
---

If you want to remove an instance, use the **DEL** action. The Name is used for identifying the instance to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o INSTANCE -a del -v "Poller test" 


Setparam
--------

If you want to change a specific parameter of an instance, use the **SETPARAM** command. The Name is used for identifying the instance to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o INSTANCE -a setparam -v "Poller test;ns_ip_address;10.30.2.99" 


Arguments are composed of the following columns:

======== ====================
Order	 Column description
======== ====================
1	 Name of instance

2	 Parameter name

3	 Parameter value
======== ====================


Parameters that you may change are:

========================== =====================================================
Column	                   Description
========================== =====================================================
name	

localhost	           *1* if it is the main poller, *0* otherwise

ns_ip_address	           IP address of the poller

ns_activate	           *1* if poller is enabled, *0* otherwise

init_script	           Init script path

monitoring_engine	   Engine used on poller: *NAGIOS*, *ICINGA*, *SHINKEN*

nagios_bin	           Path of the Scheduler binary

nagiostats_bin	           Path of the Nagios Stats binary

nagios_perfdata	           Path of perfdata file

ssh_port	           SSH Port

centreonbroker_cfg_path	   Centreon Broker Configuration path

centreonbroker_module_path Centreon Broker Module path
========================== =====================================================



Gethosts
--------

If you want to list all hosts that are monitored by a poller, use the **GETHOSTS** action. The Name is used for identifying the instance to query::

  [root@centreon ~]# ./centreon -u admin -p centreon -o INSTANCE -a GETHOSTS -v "Poller test"
  14;Centreon-Server;127.0.0.1
  17;srv-website;10.30.2.1

Returned info is the following:

================= ================================================================
Order             Description
================= ================================================================
1                 Host ID

2                 Host name

3                 Host address
================= ================================================================
