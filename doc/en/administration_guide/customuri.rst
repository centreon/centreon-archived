==========
Custom URI
==========

It is possible to update the URI of Centreon. For example, **/centreon** can be replaced by **/monitoring**.

To update the Centreon URI, you need to follow those steps:

1. Go to **Administration > Parameters > Centreon UI** and change the **Centreon Web Directory** value.

.. image:: /_static/images/adminstration/custom_uri.png
    :align: center

2. On the centreon central server:

* Remove the **centreon/www/static** folder.
* Replace **/centreon** occurences by **/your_custom_uri** in **centreon/www/index.html**.
* Replace **/centreon** occurences by **/your_custom_uri** in **centreon/www/.htaccess**.
* Navigate to your Centreon URL.
