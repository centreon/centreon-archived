===============================
Nagios Reader to Centreon CLAPI
===============================

**Nagios Reader to Centreon CLAPI** is a free and open source project to analyze
Nagios CFG configuration files and to transform monitoring configuration to
Centreon CLAPI command in order to import configuration into Centreon web
interface.

Prerequisites
=============

First of all you need a Centreon server installed and ready to use. Please see the
documentation :ref:`to install a Centreon server<installisoel7>` based on Centreon.

Installation
============
This script uses the Perl-Nagios-Object library to read CFG files. To install
it please follow this steps on your Nagios(R) server

CentOS::

  $ yum install perl-Module-Build

Debian::

  $ apt-get install libmodule-build-perl

  $ cd /tmp
  $ wget http://search.cpan.org/CPAN/authors/id/D/DU/DUNCS/Nagios-Object-0.21.20.tar.gz
  $ tar xzf Nagios-Object-0.21.20.tar.gz
  $ cd Nagios-Object-0.21.20
  $ perl Build.PL
  $ ./Build
  $ ./Build test
  $ ./Build install

Download script from GitHub on your Nagios(R) server::

  $ cd /tmp
  $ git clone https://github.com/centreon/nagiosToCentreon.git
  $ cd nagiosToCentreon

Usage
=====

On a fresh Centreon server the default poller is named "Central". If you rename it
or if you want to link this Nagios configuration to a predefined poller you
have to change the poller name on line 65::

  my $default_poller = "Central";

To display help use the command::

  $ perl nagios_reader_to_centreon_clapi.pl --help
  ######################################################
  #    Copyright (c) 2005-2015 Centreon                #
  #    Bugs to http://github.com/nagiosToCentreon      #
  ######################################################

  Usage: nagios_reader_to_centreon_clapi.pl
      -V (--version) Show script version
      -h (--help)    Usage help
      -C (--config)  Path to nagios.cfg file

To run the script please use the following command::

  $ perl nagios_reader_to_centreon_clapi.pl --config /usr/local/nagios/etc/ > /tmp/centreon_clapi_import_commands.txt

Export the file /tmp/centreon_clapi_import_commands.txt on your Centreon server.

Run the following command to import configuration into Centreon on your Centreon server::

  $ /usr/share/centreon/bin/centreon -u admin -p @PASSWORD -i /tmp/centreon_clapi_import_commands.txt

.. note::
    Replace **@PASSWORD** by password of **admin** Centreon web user.
