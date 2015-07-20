==============
Centreon 2.4.1
==============

***************
Important notes
***************

Connectors
==========

If you are already using the *Centreon Connectors*, please note that the connector
path is no longer called with user variable *$USER3$*. It is instead in the 
``Configuration`` > ``Centreon`` > ``Pollers`` > ``Centreon Connector path``. In that regard,
be sure to fill this field and update the connector command line in ``Configuration`` > 
``Commands`` > ``Connectors`` by removing the *$USER3$* prefix.

i.e::

    $USER3$/centreon_connector_perl

should become::

    centreon_connector_perl

Once you're done with updating those configurations, you may delete the former *$USER3$*
as it will be no longer used.

