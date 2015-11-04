==================
Service categories
==================

Overview
--------

Object name: **SC**

Show
----

In order to list available service categories, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a show
  id;name;description
  1;Ping;ping
  2;Traffic;traffic
  3;Disk;disk

Columns are the following:

============ ======================================
Column       Description
============ ======================================
Name         Name of service category

Description  Description of service category
============ ======================================


Add
---

In order to add a service category, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a ADD -v "Alfresco;Alfresco Services" 

Required parameters are:

============ ======================================
Column       Description
============ ======================================
Name         Name of service category

Description  Description of service category
============ ======================================


Del
---

In order to remove a service category, use the **DEL**::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a del -v "Alfresco"


Setparam
--------

In order to change parameters for a service category, use the **SETPARAM** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a setparam -v "SG1;name;Web Service" 

You can change the following parameters:

============ ======================================
Parameter    Description
============ ======================================
Name         Name of service category

Description  Description of service category
============ ======================================


Getservice and Getservicetemplate
---------------------------------

In order to view the member list of a service category, use the **GETSERVICE** or **GETSERVICETEMPLATE** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a getservice -v "Ping-Category" 
  host id;host name;service id;service description
  14;Centreon-Server;27;Ping
  27;srv-web;42;Ping

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a getservicetemplate -v "Ping-Category" 
  template id;service template description
  22;Ping-LAN
  23;Ping-WAN


Addservice, Setservice , Addservicetemplate and Setservicetemplate
------------------------------------------------------------------

In order to add a new element to a specific service category, you use the following actions: 
*ADDSERVICE**, **SETSERVICE**, **ADDSERVICETEMPLATE**, where *add* will append and *set* will overwrite previous definitions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a addservice -v "Ping-Category;my host,my service" 

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a addservicetemplate -v "Ping-Category;my template" 


Delservice and Delservicetemplate
---------------------------------

In order to remove a service from a  specific service category, use the **DELSERVICE** OR **DELSERVICETEMPLATE** actions::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a delservice -v "Ping-Category;my host,my service" 

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a delservicetemplate -v "Ping-Category;my template" 


Setseverity
-----------

In order to turn a service category into a severity, use the **SETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a setseverity -v "Critical;3;16x16/critical.gif" 

The needed parameters are the following:

============ ============================================================
Order        Description
============ ============================================================
1            Service category name

2            Severity level - must be a number

3            Icon that represents the severity
============ ============================================================


Unsetseverity
-------------

In order to turn a severity into a regular service category, use the **UNSETSEVERITY** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o SC -a unsetseverity -v "Critical" 

The needed parameters are the following:

============ ============================================================
Order        Description
============ ============================================================
1            Service category name
============ ============================================================
