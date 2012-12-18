========
Overview
========

This document explains briefly how components of the Centreon Software
Suite interact to provide their well-known monitoring experience.

A basic schema is quite simple:

.. image:: /_static/images/user/broker_single_poller.png
   :align: center

#. Users are connected to Centreon Web. This software has two core
   features. First it helps users configure their monitoring
   infrastructure and choose what to monitor. The second part is
   explained in 4). The documentation of Centreon Web is available 
   :ref:`here <user_guide>`.

#. Centreon Web send configuration orders to Centreon Engine which is
   the monitoring engine itself. Centreon Engine will run configured
   checks on an interval basis. Centreon Engine is very similar to
   Nagios (almost 100% compatible) but provide much more
   performance. Documentation about Centreon Engine and its advantages
   is available at :ref:`Centreon Engine Reference Manual
   <01-centreon/centreon_engine/documentation>`.

#. Centreon Engine does not export the information it has by
   default. This needs to be done through an additional software
   called Centreon Broker. This piece of software plugs into Centreon
   Engine and report monitoring results back to a database. Centreon
   Broker is very similar to NDOUtils but provide much more features
   (authentication, compression, encryption, ...) and
   performance. :ref:`Centreon Broker Reference Manual
   <01-centreon/centreon_broker/documentation>` is available for more
   information.

#. Database is exploited back by Centreon Web to provide tables and
   charts about the current (or past) state of the monitored
   infrastructure. Multiple filters are available so that users can,
   for example, see only currently unavailable checkpoints.

Members of the Centreon Software Suite are very versatile and
extensible. It is totally possible to build different architectures to
suite your need. For example, on large networks with many (10,000+)
checkpoints, the architecture could be like something below.

.. image:: /_static/images/user/broker_multiple_pollers.png
   :align: center

Almost anything is possible with Centreon software. If you feel like
you might need some help though, feel free to checkout `Merethis
services <http://www.merethis.com/en/services/services>`_.
