*****
Hosts
*****

Hosts are basically devices that you monitor. Most of the time they
are just servers, routers, switches, firewalls, temperature probes
etc... Anything that own an IP address and that can communicate with
the Centreon server can be monitored.

.. image:: /_static/images/user/add_host_1.png
   :align: center

Fill the main fields of the form:

.. image:: /_static/images/user/add_host_2.png
   :align: center

======================== =================================================================================
Field Names              Description                                                                     
======================== =================================================================================
Host Name                Name used for identifying the host.                                             

Alias                    Description of the host.                                                        

IP Address / DNS         IP address that will be used by most check plugins.                             

Host Templates           Templates are used for quick deployment. You can leave the parameters empty if 
                         you wish to use the ones that are set on the template. You can also set 
                         multiple templates.

Check Period             Time Period within which checks will be actively made.                          

Check Command            Check command that will be used for checking the status of the host. It is 
                         usually a ping check plugin that is behind a host check command.
Args                     ``$ARGn$`` arguments that will be used with the check command.                      

Max Check Attempts       Number of checks necessary to make sure that a Host is really DOWN (HARD state).

Normal Check Interval    The check frequency. e.g: Centreon-Server will be checked every 5 minutes.      

Retry Check Interval     The check frequency that will be used when a Host goes DOWN.                    

Notification Enabled     Whether or not notification is enabled for this Host.                           

Linked Contacts          Contacts that will be notified when the Host is subject to a status change.     

Notification Interval    Notification frequency. e.g: admin user will be notified only once.             

Notification Period      Period within which, notification will be sent out regarding the Host.          

Notification Options     Statuses for which notification will be sent out. e.g: notifications will be
                         sent out only if Centreon-Server goes DOWN.
======================== =================================================================================
