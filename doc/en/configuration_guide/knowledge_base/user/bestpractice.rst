Best practice for deployment
============================

To deploy procedures in the best way, we strongly advice you to use the
multi level inheritance system. 

The best practice is to define
procedures at template level as much as you can.

Here is an example of an host template configuration tree:

- Linux > Generic-hosts
- Windows > Generic-hosts
- RedHat > Linux 
- Debian > Linux 
- Active-Directory > Windows
- LDAP > Linux

To setup procedures for the *RedHat* host template, just proceed as
indicated in :ref:`wiki-page-link`. 

In the template tree we see that the **RehHat** template inherits from two other templates: **Linux** and **Generic-hosts**. In this example all hosts using the *RedHat* host
template will have the new procedure defined attached.

We could setup a procedure at a higher level in the template tree, it will impact more hosts. 

For example if we define a procedure for **Linux** host template, all hosts using **RedHat**, **Debian** and **LDAP** host templates will have the procedure attached by inheritance. Because **Linux** is the parent template. 

Behavior is the same for service templates.

.. warning::

   To delete a procedure link for specific host / service / template, edit the object and empty the **URL** field in **Extended Information** tab. 

   If the object inherits from any template of a procedure, the empty value will overload and delete the procedure link.



