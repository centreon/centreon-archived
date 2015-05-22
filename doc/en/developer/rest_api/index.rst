Rest API
========

The REST API is an api in JSON format.

The first step to use the api is the authentication in POST. See the documentation for arguments.

::

   http://<centreon_url>/api/authenticate

The return give a authentication token. This token must be sent in headers for every requests. The header name is **centreon-x-token**.

.. toctree::
   :maxdepth: 2

   centreon-configuration/index  
