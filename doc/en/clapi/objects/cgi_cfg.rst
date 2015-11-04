=======
CGI CFG
=======

Overview
--------

.. warning::
	Compatible with Centreon 2.4.0 and later

Object name: CGICFG


Show
----

In order to list available CGI CFG, use the **SHOW** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CGICFG -a show 
  id;name;comment;instance;activate
  10;CGI.cfg;Install Nagios TGZ;Central;1
  [...]


Columns are the following :

========= =============================
Column	  Description
========= =============================
ID	  ID

Name	  Name

Comment	  Comment

Instance  Instance that is linked to cgi.cfg

Activate  1 if activated, 0 otherwise
========= =============================


Add
---

In order to add a CGI CFG, use the **ADD** action:::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CGICFG -a add -v "cgi for poller test;my comment;Poller test" 


Required fields are:

======== ==================================
Column	 Description
======== ==================================
Name	 Name

Comment	 Comment

Instance Instance that is linked to cgi.cfg
======== ==================================


Del
---

If you want to remove a CGI configuration, use the **DEL** action. The Name is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CGICFG -a del -v "cgi for poller test" 


Setparam
--------

If you want to change a specific parameter of a CGI configuration, use the **SETPARAM** action. The Name is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o CGICFG -a setparam -v "cgi for poller test;default_user_name;nagios" 


Arguments are composed of the following columns:

=========== ============================
Order	    Column description
=========== ============================
1	    Name of CGI configuration

2	    Parameter name

3	    Parameter value
=========== ============================


Parameters that you may change are:

========================================== ============================================
Column	                                   Description
========================================== ============================================
name	

comment	

activate	                           1 when activated, 0 otherwise

instance	                           Instance that is linked to cgi.cfg

main_config_file	                   Refer to documentation*

physical_html_path	                   Refer to documentation*

url_html_path	                           Refer to documentation*

nagios_check_command	                   Refer to documentation*

use_authentication	                   Refer to documentation*

default_user_name	                   Refer to documentation*

authorized_for_system_information	   Refer to documentation*

authorized_for_system_commands	           Refer to documentation*

authorized_for_configuration_information   Refer to documentation*

authorized_for_all_hosts	           Refer to documentation*

authorized_for_all_host_commands	   Refer to documentation*

authorized_for_all_services	           Refer to documentation*

authorized_for_all_service_commands	   Refer to documentation*

statusmap_background_image	           Refer to documentation*

default_statusmap_layout	           Refer to documentation*

statuswrl_include	                   Refer to documentation*

default_statuswrl_layout	           Refer to documentation*

host_unreachable_sound	                   Refer to documentation*

host_down_sound	                           Refer to documentation*

service_critical_sound	                   Refer to documentation*

service_warning_sound	                   Refer to documentation*

service_unknown_sound	                   Refer to documentation*

ping_syntax	                           Refer to documentation*
========================================== ============================================

.. note::
	\* http://nagios.sourceforge.net/docs/nagioscore/3/en/configcgi.html
