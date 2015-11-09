.. _dependancy:

============
Dependencies
============

*********
Principle
*********

Dependencies are used to satisfy two main requirements :

* Limit the sending of notifications
* Target the alerts

The dependencies of objects are of two types:

* **Physical** dependencies between objects: a load balancing switch is situated upstream of a set of servers and downstream of a router
* **Logical** dependencies between objects: the access to a website with authentication LDAP depends on the status of the LDAP directory itself

*********************
Physical dependencies
*********************
 
Physical dependencies consist of taking into account physical links between equipment. This link can only be defined for objects of the “Host” type.

The configuration of a physical dependencies takes place in the **Relations** tab of a configuration sheet of a host (**Configuration ==> Hosts ==> Add**).

It is possible of define two settings:

* Parent hosts: signifies that the hosts selected are parents of this host (situated upstream). If all the parent hosts selected become unavailable or impossible to reach the host itself will be considered by the scheduler as impossible to reach.

* Child hosts: signifies that the host becomes the parent of all the child hosts selected.

.. note::
   All the parents of a host must be in a Not-OK status for the host itself to be considered impossible to reach. If only one access path is down (physical dependencies link), the scheduler will continue to monitor this host.

In the situation where family relationships have been defined between hosts supervised by different schedulers, it is possible:

* To prevent the establishment of a parental relationship, when changing the host form, between two hosts monitored by two different pollers.
* To authorise the establishment of this parental relationship. In this case the dependencies will not be managed by the Monitoring engine engines but by the Centreon Broker which will take into account this relationship in its correlation engine.

To prevent the establishment of this parental relationship, it is necessary to check **Enable strict mode for host parentship management** box in the menu: **Administration ==> Options**.

Conversely, if this box is not checked the parental links between hosts belonging to two different pollers can be established.

.. note:: To avoid receiving this type of notification don’t check the “Unreachable” notification filter on the hosts or on the contacts 

********************
Logical dependencies
********************

Logical dependencies consist of installing logical links between multiple objects that may or not be of different types. 
E.g.: a service is in charge of supervising the access to a web page requiring an authentication based on a LDAP. It is logical that if the LDAP server is down, the access to the web page will be difficult or even impossible. In this situation, the notification issued should only be communicated to the LDAP directory and not to the website.

Hosts 
=====

To configure a logical dependencies:

1. Go into the menu: **Configuration ==> Notifications**
2. In the left menu, under the title: **Dependencies**, click on **Hosts**
3. Click on **Add**
 
.. image:: /images/user/configuration/10advanced_configuration/03hostdependance.png
    :align: center

In this case, we have two types of host that come into play: one or more hosts (called master hosts) of which the status controls the execution and notification of other hosts (called dependent hosts). If you use the Centreon Broker, it is also possible to control the execution and notification of services (called dependent services) from master hosts.

* The **Name** and **Description** fields indicate the name and the description of the dependencies
* The **Parent relationship** field should be ignored if you use the Centreon Engine. If it is enabled, and if the dependencies links of the master host become unavailable, the dependencies in the process of creation is not taken into account.
* The **Execution Failure Criteria** field indicates the statuses of the master host(s) preventing the check of the hosts or the dependent services
* The **Notification Failure Criteria** field indicates the statuses of the master host(s) preventing the sending of notifications to the hosts or the dependent services
* The **Hostnames** list defines the master host(s)
* The **Dependent Host Names** list defines the dependent hosts
* The **Dependent Services** list defines the dependent services
* The **Comments** field can be used to comment on the dependencies

Services 
========

To add a dependencies at the services level:

1. Go into the menu: **Configuration ==> Notifications**
2. In the left menu, under the title: **Dependencies**, click on **Services**
3. Click on **Add**
 
.. image:: /images/user/configuration/10advanced_configuration/03servicedependance.png
    :align: center

In this case, we have two entities that come into play: the (“master”) services which control the execution and the notification of other (“dependent”) services. If you use Centreon Broker, it is also possible of control the execution and the notification of other hosts.

* The **Name** and **Description** fields indicate the name and description of the dependencies
* The **Parent relationship** field should be ignored if you use the Centreon Engine. If it is enabled, and if the links of dependencies of the master service become unavailable the dependencies in the process of creation is no longer taken into account.

* The **Execution Failure Criteria** field indicates the statuses of the master service(s) preventing the check of the hosts or the dependent services 
* The **Notification Failure Criteria** field indicates the statuses of the master service(s) preventing the sending of notifications to the hosts or the dependent services
* The **Services** list defines the master service(s)
* The **Dependent services** list defines the dependent services
* The **Dependent hosts** list defines the dependent hosts
* The **Comments** field can be used to comment on the dependencies

Host groups 
===========

To add a dependencies at the host groups level:

1. Go into the menu: **Configuration ==> Notifications**
2. In the left menu, under the title: **Dependencies**, click on **Host Groups**
3. Click on **Add**

.. image:: /images/user/configuration/10advanced_configuration/03hostgroupdependance.png
    :align: center
 
Two types of host groups: a host group is called a master if it controls the execution and the notification of other (“dependent”) host groups.

* The **Name** and **Description** fields indicate the name and the description of the dependencies
* The **Parent relationship** field should be ignored if you use the Centreon Engine. If it is enabled, and if the links of dependencies of the master host group become unavailable the dependencies in the process of creation is no longer taken into account.
* The **Execution Failure Criteria** field indicates the statuses of the master host group(s) preventing the check of the dependent host groups
* The **Notification Failure Criteria** field indicates the statuses of the master host(s) preventing the sending of notifications to the dependent host groups
* The **Host groups name** list defines the master host group(s)
* The **Dependent host group name** list defines the dependent host group(s)
* The **Comments** field can be used to comment on the dependencies

Service groups
==============

To add a dependencies at the service groups level:

1. Go into the menu: **Configuration ==> Notifications**
2. In the left menu, under the title: **Dependencies**, click on **Service Groups**
3. Click on **Add**

.. image:: /images/user/configuration/10advanced_configuration/03servicegroupdependance.png
    :align: center
 
Two types of service group: a service group is called a “master” if it controls the execution and the notification of other (“dependent”) service groups.

* The **Name** and **Description** fields indicate the name and the description of the dependencies
* The **Parent relationship** field should be ignored if you use the Centreon Engine. If it is enabled, and if the links of dependencies of the master service group become unavailable the dependencies in the process of creation is no longer taken into account.
* The **Execution Failure Criteria** field indicates the statuses of the master service group(s) preventing the check of the dependent service groups
* The **Notification Failure Criteria** field indicates the statuses of the master host(s) preventing the sending of notifications to the dependent service groups
* The **Service group names** list defines the group(s) of master services
* The **Dependent service group names** list defines the group(s) of dependent services
* The **Comments** field can be used to comment on the dependencies

Meta-services 
=============

To add a dependencies at the meta-services level:

1. Go into the menu: **Configuration ==> Notifications**
2. In the left menu, under the title: **Dependencies**, click on **Meta Services**
3. Click on **Add**

.. image:: /images/user/configuration/10advanced_configuration/03metaservicedependance.png
    :align: center

Two types of meta-services: a meta-service is called a “master” if it controls the execution and the notification of other (“dependent”) meta-services.

* The **Name** and **Description** fields indicate the name and description of the dependencies
* The **Parent relationship** field should be ignored if you use the Centreon Engine. If it is enabled, and if the links of dependencies of the master meta-service become unavailable the dependencies in the process of creation is no longer taken into account.
* The **Execution Failure Criteria** field Indicates which are the statuses of the meta-master service(s) that will prevent the check of the meta-dependent services
* The **Notification Failure Criteria** field indicates the statuses of the meta-service(s) preventing the sending of notifications to meta-dependent services
* The **Meta-service name** list defines the master meta-service(s)
* The **Dependent meta-service** names list defines the dependent meta-service(s) 
* The **Comments** field can be used to comment on the dependencies

