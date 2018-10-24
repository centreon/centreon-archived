==========
Custom URI
==========

It is possible to update the URI of Centreon. For example, **/centreon** can be replaced by **/monitoring**.

To update the Centreon URI, you need to follow those steps:

* Remove this folder on central server : **centreon/www/static**
* Replace **/centreon** occurences by **/your_custom_uri** in **centreon/www/index.html**
* Replace **/centreon** occurences by **/your_custom_uri** in **centreon/www/.htaccess**
* Navigate to your Centreon URL
