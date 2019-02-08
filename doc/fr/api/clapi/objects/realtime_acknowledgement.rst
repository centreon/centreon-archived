=========================
Acknowledgement temp reel
=========================

Overview
--------

Object name: **RTACKNOWLEDGEMENT**

Show host real time acknowledgement
-----------------------------------

In order to list available real time acknowledgement, use the **SHOW** action::
You can use the value "HOST" to display all the acknowledgement::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTACKNOWLEDGEMENT -a show -v "HOST;generic-host"
  id;host_name;entry_time;author;comment_data;sticky;notify_contacts;persistent_comment
  6;generic-host;2017/09/28 14:21;admin;'generic-comment';1;0;1

Columns are the following :

================================= ==================================================================================
Column	                          Description
================================= ==================================================================================
Id	                              Id of the acknowledgement

Host_name	                      Name of the host

Entry_time                        Beginning of acknowledgement

Author	                          Name of the author

Comment_data                      Short description of the acknowledgement

Sticky                            Acknowledgement will be maintained in case of a change of Not-OK status (0/1)

Notify_contacts                   Notification send to the contacts linked to the object (0/1)

Persistent_comment                Acknowledgement will be maintained in the case of a restart of the scheduler (0/1)

================================= ==================================================================================

Show service real time acknowledgement
--------------------------------------

In order to list available real time acknowledgement, use the **SHOW** action::
You can use the value "SVC" to display all the acknowledgement::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTACKNOWLEDGEMENT -a show -v "SVC;generic-host,generic-service"
  id;host_name;service_name;entry_time;author;comment_data;sticky;notify_contacts;persistent_comment
  42;generic-host;generic-service;2017/09/28 14:21;admin;'generic-comment';1;0;1

Columns are the following :

================================= ===========================================================================
Column	                          Description
================================= ===========================================================================
Id	                              Id of the acknowledgement

Host_name	                      Name of the host

Service_name	                  Name of the service

Entry_time                        Beginning of acknowledgement

Author	                          Name of the author

Comment_data                      Short description of the acknowledgement

Sticky                            Acknowledgement will be maintained in case of a change of Not-OK status (0/1)

Notify_contacts                   Notification send to the contacts linked to the object (0/1)

Persistent_comment                Acknowledgement will be maintained in the case of a restart of the scheduler (0/1)

================================= ==================================================================================

Real time Acknowledgement for : Addhost
---------------------------------------

If you want to associate a host to a real time acknowledgement, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTACKNOWLEDGEMENT -a add -v "HOST;central;my comments;1;0;1"

The required parameters are the following :

========= ====================================================================================
Order     Description
========= ====================================================================================
1         Value you want to associate

2         Name of the host (Name of the service)

3         Short description of the real time acknowledgement

4         Acknowledgement maintained in case of a change of status (Sticky)

5         Notification send to the contacts linked to the object (Notify)

6         Maintained th acknowledgement in the case of a restart of the scheduler (Persistent)

========= ====================================================================================


Real time Acknowledgement for : addservice
------------------------------------------

If you want to associate a service or service group to a real time acknowledgement, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTACKNOWLEDGEMENT -a add -v "SVC;central,ping|central,memory;my comments;1;0;1"

The required parameters are the following :

========= ====================================================================================
Order     Description
========= ====================================================================================
1         Value you want to associate

2         Name of the host , name of the service

3         Short description of the real time acknowledgement

4         Acknowledgement maintained in case of a change of status (Sticky)

5         Notification send to the contacts linked to the object (Notify)

6         Maintained th acknowledgement in the case of a restart of the scheduler (Persistent)

========= ====================================================================================


Cancel a real time acknowledgement
----------------------------------

In order to cancel a real time acknowledgement, use the **CANCEL** action::
To get the value of the id, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o RTACKNOWLEDGEMENT -a CANCEL -v "6|42"

The required parameters are the following :

========= ============================================
Order     Description
========= ============================================
1         Id of acknowledgement

========= ============================================
