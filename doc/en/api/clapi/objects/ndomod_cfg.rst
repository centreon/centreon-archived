==========
Ndomod CFG
==========

Overview
--------

Object name: **NDOMODCFG**

Show
----

In order to list available Ndomod CFG, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDOMODCFG -a show 
  id;description;instance;output type;output;tcp port
  1;Central-mod;Central;tcpsocket;127.0.0.1;5668
  [...]

Columns are the following :

============= ============================================================
Column	      Description
============= ============================================================
ID	      ID

Description   Description

Instance      Instance that is linked to ndomod.cfg

Output Type   Can be: *tcpsocket*, *file*, *unixsocket*

Output	      Depends on the output type, it can be an IP Address or a file

TCP Port	
============= ============================================================


Add
---

In order to add an Ndomod CFG, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDOMODCFG -a add -v "ndomod for poller test;Poller test" 

Required fields are:

============ =======================================
Column	     Description
============ =======================================
Description  Description

Instance     Instance that is linked to ndomod.cfg
============ =======================================


Del
---

If you want to remove a Ndomod configuration, use the **DEL** action. The Description is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDOMODCFG -a del -v "ndomod for poller test" 


Setparam
--------

If you want to change a specific parameter of an Ndomod configuration, use the **SETPARAM** action. The Description is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NDOMODCFG -a setparam -v "ndomod for poller test;output_type;tcpsocket" 


Arguments are composed of the following columns:

======== ===============================
Order	 Column description
======== ===============================
1	 Name of ndomod configuration

2	 Parameter name

3	 Parameter value
======== ===============================

Parameters that you may change are:

=========================== =================================================================================
Parameter	            Description
=========================== =================================================================================
description	            Description

output_type	            Can be: *tcpsocket*, *file*, *unixsocket*

output	                    Depends on the output type, it can be an IP Address or a file

instance	            Instance that is linked to ndomod.cfg

tcp_port	            TCP Port

output_buffer_items	    Number of items in output buffer

file_rotation_interval	    File rotation interval

file_rotation_timeout	    File rotation timeout

reconnect_interval	    Reconnect Interval

reconnect_warning_interval  Reconnect Warning Interval

data_processing_options	    Data Processing Options, -1 by default

config_output_options	    Output options, 3 by default

activate	            *1* if activated, *0* otherwise
=========================== =================================================================================
