Configuration of the scheduler
==============================

Once the installation is completed, it is necessary to integrate this remote server into the Centreon configuration.

#. Go into the menu: **Configuration** ==> **Pollers**
#. Click on **Add**
#. Change the following settings, and save:

*	Change the name of the **Poller Name**.
*	Enter the IP address of the collector in the **IP Address** field.
*	Enable the collector by clicking on **Enabled** in the **Status** field.

.. image:: /images/user/configuration/10advanced_configuration/07addpoller.png
   :align: center


#. Go into the menu: **Configuration** ==> **Pollers** ==> **Engine configuration**
#. Select your last added configuration.
#. Change the following settings, and save:

*	In the **Data** tab - **Multiple broker module** field change the name of the of Centreon Broker configuration file **central-module.xml** to for example: poller1-module.xml.

.. image:: /images/user/configuration/10advanced_configuration/07mainconffilebrokerconf.png
   :align: center

Centreon Broker configuration
=============================

It is necessary to generate a configuration file for Centreon Broker:

#. Go into the menu: **Configuration ==> Pollers ==> Centreon-Broker ==> Configuration**
#. Use **Add with wizard**
#. Choose **Simple Poller**
#. Indicates a configuration name and the Central monitoring server address
#. Click on Finish

.. image:: /images/user/configuration/10advanced_configuration/07brokerconfwizzard.png
   :align: center


Optional authentication with Centreon Broker
============================================

If you wish to authenticate pollers that are sending data to your
monitoring system then you can optionaly use Centreon Broker
authentication mechanism, which is based on X.509 certificates.

First generate a Certificate Authority certificate with OpenSSL. *ca.key*
will be the private key (to store securely), while *ca.crt* will be the
public certificate with which we will authenticate incoming connections.

::

	$> openssl req -x509 -newkey rsa:2048 -nodes -keyout ca.key -out ca.crt -days 365


Now we can generate certificates using the CA key.

::

	$> openssl req -new -newkey rsa:2048 -nodes -keyout central.key -out central.csr -days 365
	$> openssl req -new -newkey rsa:2048 -nodes -keyout poller.key -out poller.csr -days 365
	$> openssl x509 -req -in central.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out central.crt -days 365 -sha256
	$> openssl x509 -req -in poller.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out poller.crt -days 365 -sha256


Place *central.key*, *central.crt* and *ca.crt* on the Centreon central server
(in **/etc/centreon-broker** for example) and *poller.key*, *poller.crt* and
*ca.crt* on your poller.

Now we need to configure Centreon Broker to use these files. Go to
**Configuration ==> Pollers ==> Broker configuration**. For
*central-broker-master*, in the *Input* tab, you need to set the following
parameters for *central-broker-master-input*.

- Enable TLS encryption = Yes
- Private key file = /etc/centreon-broker/central.key
- Public certificate = /etc/centreon-broker/central.crt
- Trusted CA's certificate = /etc/centreon-broker/ca.crt

.. image:: /_static/images/configuration/broker_certificates.png
   :align: center

Similarly for your poller, you will need to modify it's TCP output in the Output
tab with the following parameters.

- Enable TLS encryption = Yes
- Private key file = /etc/centreon-broker/poller.key
- Public certificate = /etc/centreon-broker/poller.crt
- Trusted CA's certificate = /etc/centreon-broker/ca.crt

Regenerate the configuration of the affected pollers
(**Configuration ==> Pollers**) and you're good.


Centreontrapd Configuration
===========================

It is necessary to change the configuration files of Centreontrapd so that the service can question the SQLite database (see the chapter: :ref:`configuration_advanced_snmptrapds`).

Plugins synchronisation
=======================

You can synchronise the plugins between your central server and your remote servers using **rsync** software.

.. warning::
   Don’t perform this action if your plugins depend on third party libraries that need to have been installed previously.

Exchanging SSH keys
===================

For the central server to be able to export the configuration files of the monitoring engine, it is necessary to make a SSH key exchange between the central server and the new remote server.

On the remote server:

#. Log in as a ‘root’
#. Change the Centreon user password::

	# passwd centreon

On the central server:

1. Log in as ‘Centreon’::

    # su - centreon

2. If you have not already generated a public / private key pair, enter the following command (leave the default options)::

    $ ssh-keygen

3. Then export your SSH key to the remote server::

    $ ssh-copy-id -i /var/spool/centreon/.ssh/id_rsa.pub centreon@[POLLER_IP]

4. Check that you can log in from the central server to the remote server as a Centreon user. You can you use the command::

    $ ssh centreon@[POLLER_IP]

Export the configuration
========================

It only remains to export the configuration to verify that the installation of the remote server has been executed correctly.

.. note::
   Refer to the documentation: :ref:`Export configuration<deployconfiguration>`
