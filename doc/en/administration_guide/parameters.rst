.. _centreon_parameters:

===============================================
Administration options of the Centreon platform
===============================================

The following options enable us to change the settings of the Centreon architecture.

***********
Centreon UI
***********

This part covers the configuration of the general options of the Centreon web interface.

#. Go into the menu: **Administration > Parameters > Centreon UI**

The following window is displayed:

.. image:: /images/guide_exploitation/ecentreon.png
   :align: center

* **Directory** indicates the directory where Centreon is installed
* **Centreon Web Directory** field indicates the web directory on which Centreon is installed
* **Limit per page (default)** field defines the number of objects displayed per **Configuration** page
* **Limit per page for Monitoring** field defines the number of objects displayed per page in the **Monitoring** menu
* **Graph per page for Performances** field defines the maximum number of displayed charts on **Performance** page
* **Number of elements loaded in select** field defines the maximum number in select box
* **Sessions Expiration Time** field, expressed in minutes, indicates the maximum session duration
* **Refresh Interval for statistics** field, expressed in seconds, indicates the refreshment interval for the statistics page
* **Refresh Interval for monitoring** field, expressed in seconds, indicates the refreshment interval for the objects on the monitoring page
* **Sort problems by** field is used to choose how to sort the incidents in the **Monitoring** menu
* **Order sort problems** field indicates the display order for incidents, by rising or falling order of gravity
* **Display downtime and acknowledgment on chart** allows to display downtime and acknowledgment on chart
* **Display comment on chart** allows to display comment from service on chart
* **Enable Autologin** box authorizes the users to log into the web interface via the autologin mechanism
* **Display Autologin shortcut** box serves to display the connection short-cut at the top right
* **Enable SSO authentication** box enables SSO authentication
* **SSO mode** field indicates if the authentication should take place only by SSO or using local authentication as well (Mixed). The mixed mode requires trusted client addresses.
* **SSO trusted client addresses** field indicates which are hte IP/DNS of the trusted clients (corresponding to the reverse proxy) for SSO. The trusted clients are separated by comas.
* **SSO blacklist client addresses** field indicates which are hte IP/DNS rejected.
* **SSO login header** field indicates the variables of the header that will be used as a login / pseudo (i.e HTTP_AUTH_USER).
* **SSO pattern matching login** field indicates the pattern to search for in the username.
* **SSO pattern replace login** field indicates the replace string.
* **Timezone** field indicates timezone of your monitoring server.
* **Centreon Support Email** field indicates the e-mail address of the **Customer’s service support centre** for the Centreon platform. This e-mail address will be displayed at the bottom of the page on the link **Centreon Support**

.. warning::
    SSO feature has only to be enabled in a secured and dedicated environment for SSO. Direct access to Centreon UI from users have to be disabled.

.. _impproxy:

Proxy configuration
-------------------

The proxy configuration is mandatory to use CEntreon IMP offer.

Define needed information:

* **Proxy URL**
* **Proxy port**
* **Proxy user**
* **Proxy password**

.. image:: /_static/images/adminstration/proxy_configuration.png
    :align: center

Once you defined settings, test your configuration by clicking on the
**Text Proxy Configuration** button. If your configuration is correct,
a message will indicate success:

.. image:: /_static/images/adminstration/proxy_configuration_ok.png
    :align: center

**********
Monitoring
**********

This part covers the general options of the real time monitoring interface.

#. Go into the menu: **Administration > Parameters > Monitoring**
#. Click on **Monitoring**

.. image:: /images/guide_exploitation/esupervision.png
   :align: center

* **Interval Length** field indicates the time interval in seconds used to program the checks and notifications
* **Images Directory** field defines the image directory in which the medias are stored
* **Plugins Directory** field defines the directory where monitoring plugins are stored
* **Start script for broker daemon** field contains the path to the init script of the broker
* **Directory + Mailer Binary** field contains the path to the executable file for sending  e-mails
* **Maximum number of hosts to show** and **Maximum number of services to show** lists contain the maximum number of hosts or services to be displayed in the overall view (menu: **Home > Home**)
* **Page refresh interval** field defines the data refreshment interval in the overall view
* The boxes in the **Default acknowledgment settings** and **Default downtime settings** categories define the options by default that will be checked or not during definition of an acknowledgment or of a downtime


********
CentCore
********

This part can be used set the operation of the CentCore process.

#. Go into the menu: **Administration > Parameters > Centcore**

.. image:: /images/guide_exploitation/ecentcore.png
   :align: center

* **Enable Broker Statistics Collection** field enables the retrieval of statistics from the Centreon Broker by CentCore. This can be a blocking option because the reading of the pipe can be a blocking action
* **Timeout value for Centcore commands** field can be used to define a timeout for local commands and commands via SSH process.
* **Illegal characters for Centcore commands** field allows to define characters which will be removed from commands forwarded by the process..

.. _ldapconfiguration:

****
LDAP
****

.. note::
    If you want to use SSO for authentication, please read this :ref:`procedure <sso>`.
    You can also use Keycloack SSO using this :ref:`procedure <keycloak>`.

This part can be used to configure the connection to LDAP directories.

To add a new directory:

#. Go into the menu: **Administration > Options > LDAP**
#. Click on **Add**

.. image:: /images/guide_exploitation/eldap.png
   :align: center

* **Configuration name** and **Description** fields define the name and the description of the LDAP server
* **Enable LDAP authentication** field serves to enable authentication via the LDAP server
* **Store LDAP password** field can be used to store user passwords in the database, useful to authenticate users in the event of loss of connection with the LDAP
* **Auto import users** field used to import the users of the LDAP directory automatically into Centreon. By clicking on **Import users manually**, you can chose the users that you want to import

.. note::
   If the **Auto import users** option is checked, the LDAP settings of any new user who logs into the Centreon interface will automatically be imported into Centreon (name, first name, e-mail address, etc.). ACL profiles will be applied on access (link to :ref:`ACL <acl>`). However, if this option is not checked, only the users imported manually will be able to authenticate.

* **LDAP search size limit** field can be used to limit the size of user searches.
* **LDAP search timeout** field can be used define the maximum time for the LDAP search.
* **Contact template** field defines the contact template that will be linked to all the users imported from this LDAP directory.
* **Default contactgroup** optional field, which is used to add a new user to a default contactgroup.
* **Use service DNS** field indicates if it is necessary to use the DNS server to solve the IP address of the LDAP directory.

.. image:: /images/guide_exploitation/eldap2.png
    :align: center

* **Enable LDAP synchronization on login** If enabled, a user LDAP synchronization will be performed on login to update contact's data and calculate new Centreon ACLs.
* **LDAP synchronization interval (in hours)** Displayed only if the previous option is enabled. This field is used to specify the time between two LDAP synchronization.

.. note::

   The contact's LDAP data won't be updated in Centreon until the next synchronization is expected. If needed, "on-demand" synchronization are available from the **Administration > Session** page and from the **Configuration > Users > Contact / Users** list.

   The interval is expressed in hours. By default, this field is set to the lower value : 1 hour.

.. note::
   We save a timestamp as reference date in the DB and use the CentAcl CRON to update it.

   The reference date is used to calculate the next expected LDAP synchronization.

   If you modify one of these two fields the reference timestamp will be reset to your current time.

   The reference date won't be updated if you modify or not, only the other fields / options.

.. image:: /images/guide_exploitation/eldap3.png
   :align: center

* **LDAP servers** field can be used to add one or more LDAP directories to which Centreon will connect

The table below summarizes the settings to add an LDAP server:

+-------------------------+------------------------------------------------------------------------------------------------------------+
|   Column                |  Description                                                                                               |
+=========================+============================================================================================================+
| Host address            | Contains the IP address or DNS  name of the LDAP server                                                    |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Port                    | Indicates the connection port to access the LDAP                                                           |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| SSL                     | Indicates if the SSL protocol is used for the connection to the server                                     |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| TLS                     | Indicates if the TLS protocol is used for the connection to the server                                     |
+-------------------------+------------------------------------------------------------------------------------------------------------+

.. image:: /images/guide_exploitation/eldap4.png
   :align: center

* **Bind user** and **Bind password** fields define the user name and the password for logging to the LDAP server
* **Protocol version** field indicates the version of the protocol using to login
* **Template** list can be used to pre-configure the search filters for users on the LDAP directory. These filters
  serve to propose, by default, a search on the MS Active Directory, Okta or of Posix type directories.

.. note::
   Before any import, check the default settings proposed. If you have not selected a Model, you will need to define the search filters manually by filling in the fields.

.. note::
    You can use **Okta** as LDAP server with `SWA plugin <https://help.okta.com/en/prod/Content/Topics/Apps/Apps_Configure_Template_App.htm>`_.
    Please define:
    
    * **uid=<USER>,dc=<ORGANIZATION>,dc=okta,dc=com** for **Bind DN** field
    * **ou=<OU>,dc=<ORGANIZATION>,dc=okta,dc=com** ** for **Search group base DN** field.

With CentOS 7, it's possible to not check server certificate, follow procedure :

Add the following line in file "/etc/openldap/ldap.conf" :

::

  TLS_REQCERT never

Then restart Apache :

::

  # systemctl restart httpd24-httpd

*******
RRDTool
*******

This part can be used to configure the RRDTool graphs generation engine.
Go into the **Administration > Parameters > RRDTool** menu.

.. image:: /images/guide_exploitation/errdtool.png
   :align: center

* **Directory + RRDTOOL Binary** field defines the path to the RRDTool executable
* **RRDTool Version** allows to know the version of RRDTool
* **Enable RRDCached** field serves to enable the RRDcached process (only works with the Centreon Broker)
* **TCP Port** field defines the port on which RRDcached listens.
* **UNIX Socket path** field defines the path to the Unix socket

.. warning::
   Don’t enable RRDCacheD unless your monitoring platform encounters too many
   disk accesses concerning the writing of data in RRD files.
   Select only one option: TCP or Unix socket.

*****
Debug
*****

This part can be used to configure the enabling of the logging of activity on Centreon processes.

#. Go into the menu: **Administration > Parameters > Debug**

.. image:: /images/guide_exploitation/edebug.png
   :align: center

* **Logs Directory** field defines the path where event logs will be recorded
* **Authentication debug** box can be used to log authentications to the Centreon interface
* **Monitoring Engine Import debug** box enables logging of the scheduler debugging
* **RRDTool debug** box enables logging of the RRDTool graph engine debugging
* **LDAP User Import debug** box enables logging of debugging of the import of LDAP users
* **SQL debug** box enables the logging of SQL requests executed by the Centreon interface
* **Centcore Engine debug** box enables logging of Centcore process debugging
* **Centreontrapd debug** box enables logging of the Centreontrapd process debugging
