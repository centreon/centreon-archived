==================
LDAP configuration
==================

Overview
--------

Object name: **LDAP**


Show
----

In order to list available LDAP configurations, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a show
  id;name;description;status
  3;ad;my ad conf;1
  2;openldap;my openldap conf;1
  [...]

Columns are the following:

======= ===============================
Order	Description
======= ===============================
1	    ID

2	    Configuration name

3       Configuration description

4       1 when enabled, 0 when disabled
======= ===============================


Add
---

In order to add an LDAP configuration, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a add -v "my new configuration;my description" 

Required fields are:

======= =============================
Order	Description
======= =============================
1	    Configuration name

2       Configuration description
======= =============================


Del
---

If you want to remove an LDAP configuration, use the **DEL** action. The Configuration Name is used for identifying the LDAP configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a del -v "my new configuration" 



Setparam
--------

If you want to change a specific parameter of an LDAP configuration, use the **SETPARAM** action. The Configuration Name is used for identifying the LDAP configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a SETPARAM -v "my new configuration;description;my new desc" 


Parameters use the following order:

======= ============================
Order   Description
======= ============================
1       Configuration to update

2       Parameter key

3       Parameter value
======= ============================


Parameters that you may change are the following:

========================== ============================================
Key                        Description
========================== ============================================
name                       Configuration name

description                Configuration description

enable                     1 when enabled, 0 when disabled

alias                      Alias

bind_dn                    Bind DN

bind_pass                  Bind password

group_base_search          Group base search

group_filter               Group filter

group_member               Group member

group_name                 Group name

ldap_auto_import           Enable or disable auto import (0 or 1)

ldap_contact_tmpl          Contact template to use on import

ldap_dns_use_domain        Use domain or not (0 or 1)

ldap_search_limit          Search size limit

ldap_search_timeout        Timeout delay (in seconds)

ldap_srv_dns               DNS server (only used when 
                           ldap_dns_use_domain is set to 1)

ldap_store_password        Store password in database or not (0 or 1)

ldap_template              Possible values: Posix, Active Directory

protocol_version           Protocl version (2 or 3)

user_base_search           User base search

user_email                 User email

user_filter                User filter

user_firstname             User firstname

user_lastname              User lastname

user_name                  User name

user_pager                 User phone number

user_group                 User group
========================== ============================================


Showserver
----------

In order to show the server list of an LDAP configuration, use the **SHOWSERVER** action. The Configuration Name is used for identifying the LDAP configuration to query::

   [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a SHOWSERVER -v "openldap"
   id;address;port;ssl;tls;order
   2;10.30.2.3;389;0;0;1


Addserver
---------

In order to add a server to an LDAP configuration, use the **ADDSERVER** action::

   [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a ADDSERVER -v "openldap;10.30.2.15;389;0;1"

Required parameters are the following:

============= ===============================
Order         Description
============= ===============================
1             Configuration name

2             Server address             

3             Server port

4             Use SSL or not

5             Use TLS or not
============= ===============================


Delserver
---------

In order to remove a server from an LDAP configuration, use the **DELSERVER** action. The server ID is used for identifying the server to delete::

    [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a DELSERVER -v 2


Setparamserver
--------------

In order to update the server parameters of an LDAP configuration, use the **SETPARAMSERVER** action. The server ID is used for identifying the server to update::

    [root@centreon ~]# ./centreon -u admin -p centreon -o LDAP -a SETPARAMSERVER -v "2;use_ssl;1"


Parameters that you may update are the following:

============== ======================== ========================
Key            Description              Possible values
============== ======================== ========================
host_address   Address of the server

host_port      Port of the server

host_order     Priority order
               in case of failover

use_ssl        Use SSL or not           0 or 1

use_tls        Use TLS or not           0 or 1
============== ======================== ========================
