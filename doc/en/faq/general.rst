==========================
About the new Release Plan
==========================

**Why is the new version called 18.10 instead of 2.9?**

There are two reasons. To make it easier to support Centreon, all software
components and modules now use the same version number as the Centreon solution.
And because we will now release one new version every six months, this version
number follows the YY.MM format, where YY is the year of release, MM is the month.
This is further explained in `this blog post <https://www.centreon.com/en/blog/centreon-18-10-new-versioning-scheme-and-more/>`_.

**How long will Centreon 3.4.6 / Centreon Web 2.8.x be supported?**

We will fix critical bugs on Centreon 3.4.6 and its software components, such as
Centreon Web 2.8.x, until October 2019.

**How long will Centreon 18.10 be supported?**

We will fix software bugs on Centreon 18.10 until April 2020.

**When can I expect the next version of Centreon?**

The next version of Centreon will be released in April 2019 and be called Centreon 19.04.

**Can you provide Roadmaps of incoming versions?**

Centreon will release one new version every six months.
:ref:`Please see this chapter.<life_cycle>`

===========================
Upgrading to Centreon 18.10
===========================

**Which Centreon software version can be upgraded to Centreon 18.10?**

Platforms running Centreon 2.6, 2.7 and 2.8 can easily be upgraded to Centreon
18.10. When running older versions of Centreon, it may be wise to first upgrade
to Centreon 2.6 and then to 18.10.

**I’m running Centreon open source version 2.x, can I freely upgrade to 18.10?**

Yes, you can upgrade to Centreon open source 18.10, which is free of charge.

**I’m running Centreon EPP, MAP, BAM and/or MBI, can I upgrade to 18.10?**

If you have a valid support contract, you are entitled to upgrade your platform
to Centreon 18.10. You must contact the support team to get access to the new
repositories. You will also need new software license keys.

**I’m running Centreon EPP, MAP, BAM and/or MBI, are the current version of
these modules compatible with Centreon 18.10?**

No, you should upgrade your entire platform to Centreon 18.10 and thus upgrade
these modules to their new 18.10 version.

**I purchased an online subscription to the IMP solution, can I upgrade to 18.10?**

Yes, if you have a valid IMP subscription you are entitled to upgrade your
platform to Centreon 18.10.

**Which operating system is Centreon 18.10 based on?**

Centreon 18.10 is based on CentOS 7 and is not compatible with older versions
of CentOS.

**I’m running a Centreon platform based on CentOS 6, can I upgrade to 18.10?**

Yes, you may apply a migration procedure to migrate your Centreon from a version
based on CentOS 6 to Centreon 18.10.
:ref:`Please see this chapter.<upgradecentreon1904>`

**What is the difference between updating and migrating a Centreon Server?**

If your platform is already based on CentOS 7, a simple software update is enough
to upgrade it to Centreon 18.10. If your platform is still based on CentOS 6, a
migration procedure is required to upgrade it to 18.10.
:ref:`Please see this chapter.<upgradecentreon1904>`

**Where can I find the procedure to update my Centreon Server?**

:ref:`Please see this chapter.<upgrade>`

**Where can I find the procedure to migrate my Centreon Server?**

:ref:`Please see this chapter.<upgradecentreon1904>`

**When migrating from CentOS 6 to CentOS 7, should I migrate the Centreon Pollers
at the same time as the Central Server?**

Centreon Pollers may be migrated one at a time. Centreon 18.10 Central Server is
compatible with the previous version of Centreon Pollers.

**Some of my Centreon Pollers use the optional Poller Display module, when upgrading
to Centreon 18.10 should I upgrade them to the new Remote Server functionality?**

Yes, Poller Display is not compatible with Centreon 18.10. This is further explained
in the Remote Server section of this FAQ.

========================================================
Software License keys for Centreon EPP, MAP, BAM and MBI
========================================================

**I’m running Centreon EPP, MAP, BAM and/or MBI, why do I need to change my
software license keys when upgrading to Centreon 18.10?**

The technology we use for software license keys and the format of license keys
has changed with Centreon 18.10. Older license keys are not compatible with
Centreon 18.10.

**Where can I get new software license keys?**

Please contact the support team. You will be asked for your server fingerprint.

**Where can I find the fingerprint of my Centreon Server?**

In the Centreon user interface, access to **Administration > Extensions >
Subscription** menu.

======================
Centreon Remote Server
======================

**Is Remote Server included in the open source version of Centreon?**

Yes, the new Centreon Remote Server functionality is included in the Centreon
18.10 open source, free-to-download solution.

**Is Remote Server in addition to Poller Display or replacing it?**

Centreon Remote Server is replacing the Poller Display module. The Poller Display
module is not compatible with Centreon 18.10. The Centreon Remote Server
functionality is an integral part of Centreon 18.10 and does not require any
additional module.

**What is the difference between Poller Display and Remote Server?**

Poller Display is an additional module to Centreon, whereas Centreon Remote Server
is an integral part of Centreon 18.10. Adding and configuring a Centreon Remote
Server is done in four simple steps from the Centreon graphical user interface.
Centreon Remote Server combines features from both Poller Display version 1.5
and 1.6 in a better integrated, more robust package.

**Is Poller Display compatible with Centreon 18.10?**

The Poller Display module is not compatible with Centreon 18.10.

**How can I upgrade from Poller Display to Remote Server?**

:ref:`Please see this chapter.<migratefrompollerdisplay>`

==============================================
Customer Experience Improvement Program (CEIP)
==============================================

**Where can I find information on the Centreon Customer Experience Improvement
Program (CEIP)?**

A dedicated FAQ is available in :ref:`the documentation<ceip>`.

