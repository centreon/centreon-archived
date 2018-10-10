.. _life_cycle:

=========================
Lifecycle Products Policy
=========================

Starting with Centreon 18.10, the Centreon company will publish new releases of
Centreon on a regular cadence, enabling the community, businesses and developers
to plan their roadmaps with certainty of access to newer open source upstream
capabilities.

*************************
Version numbers are YY.MM
*************************

Releases of Centreon get a version by the year and the month of delivery. For
example, Centreon 18.10 was released in October 2018. All modules and components
of the Centreon software collection have the same versioning.

***************
Release cadence
***************

The Centreon company plans to deliver 2 release by year. The first one in April
and the second one in October. Between these two major releases, Centreon will
deliver continuous minors releases including security fixes, bug fixes and
enhancements.

********************************
Maintenance and security updates
********************************

The lifecycle of a version is divided into 3 phases:

#. First phase: bugs of all severity (minor, major, critical, blocking) and security issues are fixed by priority
#. Second phases: major, critical and blocking bugs or security issues are fixed by priority
#. Third phase: blocking bugs or security issues are fixed by priority

.. note::
    The severity and prioritization of bugs are Centreon’s team responsability

The second phase of a version starts when the next major version is available.
For example, the release of Centreon 19.04 starts the second phase of Centreon
18.10.

The third phase of a version starts when the second next major version is
available. For example, the release of Centreon 19.10 starts the third phase
of Centreon 18.10 and the second phase of Centreon 19.04.

This schema presents the Centreon lifecycle:

.. image:: /_static/images/lifecycle.png
    :align: center
    :scale: 65%

******************************
Old products maintenance table
******************************

.. note::
    All other products not described in the following tables are no more supported
    by Centreon

Centreon OSS 3.4
================

+-------------------------+----------+--------------+----------------+-----------------------------+
| Product                 | Version  | Release date | End of life    | State                       |
+=========================+==========+==============+================+=============================+
| Centreon Web            | 2.8.x    | 2016/11/14   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon Engine         | 1.8.x    | 2017/09/19   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon Broker         | 3.0.x    | 2016/11/14   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon DSM            | 2.x      | 2014/09/01   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon Open Tickets   | 1.2.x    | 2016/06/20   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon AWIE           | 1.x      | 2018/04/11   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon Poller Display | 1.5.x    | 2018/04/11   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon Widgets        | 1.x      | N/A          | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+
| Centreon Plugins        | YYYYMMDD | N/A          | Centreon 20.04 | Blocking & security issues  |
+-------------------------+----------+--------------+----------------+-----------------------------+

Centreon IMP 3.4
================

+-------------------------------+---------+--------------+----------------+-----------------------------+
| Product                       | Version | Release date | End of life    | State                       |
+===============================+=========+==============+================+=============================+
| Centreon OSS                  | 3.4     | 2016/11/14   | Centreon 20.04 | Blocking & security issues  |
+-------------------------------+---------+--------------+----------------+-----------------------------+
| Centreon License Manager      | 1.1.x   | 2018/02/23   | Centreon 20.04 | Blocking & security issues  |
+-------------------------------+---------+--------------+----------------+-----------------------------+
| Centreon Plugin Packs Manager | 2.4.x   | 2018/05/30   | Centreon 20.04 | Blocking & security issues  |
+-------------------------------+---------+--------------+----------------+-----------------------------+
| Plugin Packs                  | 3.x     | N/A          | N/A            | All issues                  |
+-------------------------------+---------+--------------+----------------+-----------------------------+

Centreon EMS 3.4
================

+-------------------------+---------+--------------+----------------+-----------------------------+
| Product                 | Version | Release date | End of life    | State                       |
+=========================+=========+==============+================+=============================+
| Centreon IMP            | 3.4     | 2016/11/14   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+---------+--------------+----------------+-----------------------------+
| Centreon BAM            | 3.6.x   | 2018/02/22   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+---------+--------------+----------------+-----------------------------+
| Centreon MAP            | 4.4.x   | 2017/01/02   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+---------+--------------+----------------+-----------------------------+
| Centreon MBI            | 3.2.x   | 2018/07/09   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+---------+--------------+----------------+-----------------------------+
| Centreon Auto Discovery | 2.3.x   | 2017/08/24   | Centreon 20.04 | Blocking & security issues  |
+-------------------------+---------+--------------+----------------+-----------------------------+
