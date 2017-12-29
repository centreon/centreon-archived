==========
Add a user
==========

A Centreon user is both a contact who can be notified of an alert of a host or 
service and someone who can connect to the Centreon web interface.

First :ref:`connect<centreon_login>` to your Centreon web interface with an 
administrator account or an account which allow to manage monitored object.

Go to the **Configuration > Users > Contacts / Users** menu and click on **Add** button:

.. image:: /_static/images/quick_start/add_user_menu.png
    :align: center

You access to a form to define your information but don't worry all fields are not necessary!

The form is divided into several sections:

* The first part to set notifications options for events of hosts and services
* A second part to define the credentials to access to the Centreon web interface
* A final section to set additional options

Mandatory options
=================

On the first tab **General Information** define:

* your **Alias**, use as a login to connect to Centreon web interface 
* your **Full Name**
* your **Email** address

.. image:: /_static/images/quick_start/add_user_general_options.png
    :align: center

Notifications options
=====================

To receive notifications you have to fill some parameters:

* **Enable Notifications** allows to receive notification
* for **Host Notification Options** field select the status that you want to receive, for example: Down, Recovery, Flapping, Downtime Scheduled
* for **Host Notification Period** select the time slot during which you'll receive notifications, for example: 24x7
* for **Host Notification Commands** select how you will be notified, for example: host-notify-by-email
* for **Service Notification Options** field select the status that you want to receive, for example: Warning, Unknown, Critical, Recovery, Flapping, Downtime Scheduled
* for **Service Notification Period** select the time slot during which you'll receive notifications, for example: 24x7
* for **Service Notification Commands** select how you will be notified, for example: service-notify-by-email

.. image:: /_static/images/quick_start/add_user_notification_options.png
    :align: center

Access to Centreon web interface
================================

To connect to Centreon web interface you have to fill information:

* **Reach Centreon Front-end** allows to connect to web interface
* define your **Password** and **Confirm Password**
* define your **Timezone / Location**
* define if you are **Admin** (full access to all menus and options in Centreon web interface) or not

.. image:: /_static/images/quick_start/add_user_access_options.png
    :align: center

Save the modification by clicking on **Save** button.

.. image:: /_static/images/quick_start/add_user_list.png
    :align: center

Depending on the configuration you made your account is ready to receive notification and/or connect to the Centreon web interface.
