================
Service groups
================

Overview
--------

Object name: **SG**


Show
----

In order to list available servicegroups, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a show
  id;name;alias
  11;Alfresco;Alfresco Services


Add
---

In order to add a servicegroup, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a ADD -v "Alfresco;Alfresco Services" 

Required fields are:

====== =======================================
Order  Description
====== =======================================
1      Name of service group

2      Alias of service group
====== =======================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Del
---

In order to remove a servicegroup, use the **DEL** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a del -v "Alfresco" 

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.



Setparam
--------

In order to change parameters for a servciegroup, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a setparam -v "SG1;name;Web Service"

You can change the following parameters:

========= =========================================
Parameter Description
========= =========================================
activate  *1* when service is enabled, 0 otherwise
name      Name of service group
alias     Alias of service group
comment   Comments regarding service group
========= =========================================

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Getservice and Gethostgroupservice
----------------------------------

In order to view the members of a service group, use the **GETSERVICE** or **GETHOSTGROUPSERVICE** actions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a getservice -v "Web-Access" 
  host id;host name;service id;service description
  14;Centreon-Server;28;http
  14;Centreon-Server;29;TCP-80

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a gethostgroupservice -v "Web-Access" 
  hostgroup id;hostgroup name;service id;service description
  22;Web group;31;mysql

.. note::
  *hostgroupservice* is a service by hostgroup


Addservice, Setservice, Addhostgroupservice and Sethostgroupservice
-------------------------------------------------------------------

In order to add a new element to a specific service group, you can use **ADDSERVICE**, **SETSERVICE**, **ADDHOSTGROUPSERVICE**, **SETHOSTGROUPSERVICE** where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a addservice -v "Web-Access;www.centreon.com,http" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a setservice -v "Web-Access;www.centreon.com,TCP-80|www.centreon.com,http|www.centreon.com,mysql" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a sethostgroupservice -v "Web-Access;web group,TCP-80" 

.. note::
  *hostgroupservice* is a service by hostgroup

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


Delservice and Delhostgroupservice
----------------------------------

In order to remove a service from a service group, use the **DELSERVICE** or **DELHOSTGROUPSERVICE** actions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a delservice -v "Web-Access;www.centreon.com,http" 
  [root@centreon ~]# ./centreon -u admin -p centreon -o SG -a delhostgroupservice -v "Web-Access;Web group,mysql" 

.. note::
  *hostgroupservice* is a service by hostgroup

.. note::
  You need to generate your configuration file and restart monitoring engine in order to apply changes.


