==========
Categories
==========

Categories are used to define ACLs on the hosts and the services. The aim is to be able to classify the hosts or the services within a category.

Centreon 2.4 includes a new functionality called “Severity”. As from version 2.5, the levels of criticality are linked to a category, they have become a type of category. A criticality level is an indicator for defining the criticality of a host or a service. The aim is to be able to handle the problems of hosts or services by order of priority. By this system, it is thus possible to filter the objects in the “Supervision” views by criticality.

.. _hostcategory:

***************
Host categories
***************

To add a category of hosts:

1. Go into the menu: **Configuration ==> Hosts**
2. In the left menu, click on **Categories**
3. Click on **Add**

.. image:: /images/user/configuration/08hostcategory.png
   :align: center
 
* The **Host Category Name** and **Alias** fields contain respectively the name and the alias of the category of host.
* The **Linked hosts** list allows us to add hosts to the category.
* If a host template is added to **Linked host template** list all the hosts which inherit from this Model belong to this category.
* The **Severity type** box signifies that the category of hosts has a criticality level.
* The **Level** and **Icon** fields define a criticality level and an associated icon respectively.
* The **Status** and **Comment** fields allow us to enable or disable the category of host and to comment on it.

**********************
Categories of services
**********************

To add a category of services:

1. Go into the menu: **Configuration ==> Services**
2. In the left menu, click on **Categories**
3. Click on **Add**

.. image:: /images/user/configuration/08servicecategory.png
      :align: center
 
* The **Name** and **Description** fields define the name and the description of the category of service.
* if a service template is added to **Service Template Descriptions** list all the services which inherit from this Model belong to this category. 
* The **Severity type** box signifies that the category of service has a criticality level.
* The **Level** and **Icon** fields define a criticality level and an associated icon respectively.
* The **Status** field allows us to enable or disable the category of services.

.. note::
   For more information refer to the associated chapter covering :ref:`categories<categoriesandgroups>`.
