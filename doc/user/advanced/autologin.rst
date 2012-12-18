=========
Autologin
=========

This guide aims to explain how to set up an autologin in Centreon.

First, go to your profile settings:

.. image:: /_static/images/user/advanced/autologin_1.png
   :align: center

Here, you can either define your autologin key or generate a random one:

.. image:: /_static/images/user/advanced/autologin_2.png
   :align: center

Save your profile when the autologin key is defined.

Then, you need to activate the autologin mode:

.. image:: /_static/images/user/advanced/autologin_3.png
   :align: center

Tick the two checkboxes and save the settings.

You should now see a new icon showing next to the profile link:

.. image:: /_static/images/user/advanced/autologin_4.png
   :align: center

You are now ready to test the autologin:

  * Go to the target page, for example [Monitoring] > [Services] > [All Services]. 
  * Right click on the autologin icon > ``Copy Link Address``
  * Either logging out from Centreon or open a new web browser
  * Paste the link in the address bar 
  * You should be able to reach the target page without having to log in manually
