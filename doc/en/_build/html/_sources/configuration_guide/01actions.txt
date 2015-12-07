===============
Generic actions
===============

In the Configuration menu it is possible to perform certain “generic” actions on the various objects.

************
Add / Delete
************

The addition of a new object is done via the **Add** instruction next to the **More actions menu...**.

To delete an object :

#.	Select the object(s) that you want to delete by checking the box(s) next to its name.
#.	In the **More actions...** menu click on **Delete**.


.. warning::
   Deletion of an object is final. If you delete an object by accident, you will need to re-create it.
   In the same way, deletion of an object automatically deletes all the objects linked to it and which cannot live without it.
   E.g.: Deletion of a host results in the deletion of all the services associated with this host.

To modify an object, click on its name.

***********
Duplication
***********

Principle
=========

Duplication of an object enables it to be copied / cloned to be able to re-use its Attributes for the creation of a new object. 
E.g.: I have 10 identical web servers to supervise:

*	I add the first web server with all the necessary Attributes
*	I duplicate this host 9 times
*	It only remains for me to change the host names and the IP addresses of each duplication to adapt it to the 9 other web servers to be monitored

Thanks to this method, it is no longer necessary to create each host individually.

Practice
========

To duplicate a host:


1.	Select  the host that you want to duplicate
2.	In the **Options column**, enter the number of duplications that you want to obtain

.. image :: /images/user/configuration/01duplicate.png
   :align: center 

3.	In the **More actions...** menu click on **Duplicate**

.. image :: /images/user/configuration/01duplicateobjects.png
   :align: center

**************
Massive Change
**************

Principle
=========

Massive change enable us to apply a change to multiple objects.

E.g.: All the web servers previously created change SNMP communities.
A massive change enables us to change this community without it being necessary to change each sheet of each host individually.

Practice
========

To perform a massive change:

#.	Select the objects you want change
#.	In the **More Actions...** menu click on **Massive Change**

The change menu opens, there are 2 types to change :

*	Incremental: signifies that the change will be added to the existing options
*	Replacement: signifies that the change will overwrite the existing options

****************
Enable / disable
****************

Principle
=========

The enabling and disenabling of objects enables us to take the object into account or not during configuration generation. 
The main advantage is to be able to keep the configuration of an object without applying it.

Practice
========

To enable / disable an object:

#.	Select the objects you want change
#.	In the **more actions...**  menu click on **Enable / disable**

It is also possible to enable or disable an object via the “Status” field of the object detail sheet or by using the following icons:

*	|enabled| to enable
*	|disabled| to disable

.. |enabled|    image:: /images/enabled.png
.. |disabled|    image:: /images/disabled.png
