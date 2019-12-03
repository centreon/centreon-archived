===========
API Rest v1
===========

------------
Introduction
------------

Welcome to the Centreon API rest documentation. This documentation is for developers familiar with HTTP requests and JSON. It explains various API operations, related request and response structure, and error codes.
If you are not familiar with the JSON API, we recommend you to use the Centreon command line API documentation.

This documentation is available in english only.

-----------
Permissions
-----------

To perform API calls using a specific Centreon user, you need permissions to do so.

There are two types of permission:

You can give access to the configuration for a specific Centreon user. To do so you have
to edit user settings on the menu **Configuration > Users > Contacts/Users**,
edit user and on second tab check box **Reach API Configuration**.

You can give access to the realtime for a specific Centreon user. To do so you have
to edit user settings on the menu **Configuration > Users > Contacts/Users**,
edit user and on second tab check box **Reach API Realtime**.

If you want both then check **both** checkboxes

--------------
Authentication
--------------

Using POST method and the URL below: ::

 api.domain.tld/centreon/api/index.php?action=authenticate

Body form-data:

+-----------------+------------------+---------------------+
| Parameter       | Type             | Value               |
+-----------------+------------------+---------------------+
| username        | Text             | The user name you   |
|                 |                  | use to login on     |
|                 |                  | Centreon            |
+-----------------+------------------+---------------------+
|                 | Text             | Your Centreon       |
| password        |                  | password            |
|                 |                  |                     |
+-----------------+------------------+---------------------+

The response is a json flow getting back the authentication token  ::

  {
  "authToken": "NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc="
  }

This token will be used later on the other API actions.

--------------------
Realtime information
--------------------

Host Status
===========

All monitoring information regarding hosts are available in throw the Centreon API.

Using GET method and the URL below:  ::

 api.domain.tld/centreon/api/index.php?object=centreon_realtime_hosts&action=list

**Header:**

+---------------------+---------------------------------+
|  key                |   value                         |
+=====================+=================================+
| Content-Type        | application/json                |
+---------------------+---------------------------------+
| centreon-auth-token | the value of authToken you got  |
|                     | on the authentication response  |
+---------------------+---------------------------------+

**Parameters**

You can pass a list of parameters in order to select the data you want.

+----------------+--------------------------------------------+
|  Parameters    |   values                                   |
+================+============================================+
| viewType       | select the predefined filter like in the   |
|                | monitoring view: all, unhandled, problems  |
+----------------+--------------------------------------------+
| fields         | the fields list that you want to get       |
|                | separated by a ","                         |
+----------------+--------------------------------------------+
| status         | the status of hosts that you want to get   |
|                | (up, down, unreachable, pending, all)      |
+----------------+--------------------------------------------+
| hostgroup      | hostgroup id filter                        |
+----------------+--------------------------------------------+
| instance       | instance id filter                         |
+----------------+--------------------------------------------+
| search         | search pattern applyed on host name        |
+----------------+--------------------------------------------+
| criticality    | a specific criticity                       |
+----------------+--------------------------------------------+
| sortType       | the sortType (selected in the field list)  |
+----------------+--------------------------------------------+
| order          | ASC ou DESC                                |
+----------------+--------------------------------------------+
| limit          | number of line you want                    |
+----------------+--------------------------------------------+
| number         | page number                                |
+----------------+--------------------------------------------+

Field list :

+--------------------------+------------------------------------------+
| Fields                   | Description                              |
+==========================+==========================================+
| id                       | host id                                  |
+--------------------------+------------------------------------------+
| name                     | host name                                |
+--------------------------+------------------------------------------+
| alias                    | host alias (description of the host)     |
+--------------------------+------------------------------------------+
| address                  | host address (domain name or ip)         |
+--------------------------+------------------------------------------+
| state                    | host state (UP = 0, DOWN = 2, UNREA = 3) |
+--------------------------+------------------------------------------+
| state_type               | host state type (SOFT = 0, HARD = 1)     |
+--------------------------+------------------------------------------+
| output                   | Plugin output - state message            |
+--------------------------+------------------------------------------+
| max_check_attempts       | maximum check attempts                   |
+--------------------------+------------------------------------------+
| check_attempt            | current attempts                         |
+--------------------------+------------------------------------------+
| last_check               | last check time                          |
+--------------------------+------------------------------------------+
| last_state_change        | last time the state change               |
+--------------------------+------------------------------------------+
| last_hard_state_change   | last time the state change in hard type  |
+--------------------------+------------------------------------------+
| acknowledged             | acknowledged flag                        |
+--------------------------+------------------------------------------+
| instance                 | name of the instance who check this host |
+--------------------------+------------------------------------------+
| instance_id              | id of the instance who check this host   |
+--------------------------+------------------------------------------+
| criticality              | criticality fo this host                 |
+--------------------------+------------------------------------------+
| passive_checks           | accept passive results                   |
+--------------------------+------------------------------------------+
| active_checks            | active checks are enabled                |
+--------------------------+------------------------------------------+
| notify                   | notification is enabled                  |
+--------------------------+------------------------------------------+
| action_url               | shortcut for action URL                  |
+--------------------------+------------------------------------------+
| notes_url                | shortcut for note URL                    |
+--------------------------+------------------------------------------+
| notes                    | note                                     |
+--------------------------+------------------------------------------+
| icon_image               | icone image for this host                |
+--------------------------+------------------------------------------+
| icon_image_alt           | title of the image                       |
+--------------------------+------------------------------------------+
| scheduled_downtime_depth | scheduled_downtime_depth                 |
+--------------------------+------------------------------------------+
| flapping                 | is the host flapping ?                   |
+--------------------------+------------------------------------------+

Using GET method and the URL below:  ::

  api.domain.tld/centreon/api/index.php?object=centreon_realtime_hosts&action=list&limit=60&viewType=all&sortType=name&order=desc&fields=id,name,alias,address,state,output,next_check

Service Status
==============

All monitoring information regarding services are available in throw the Centreon API. With this call, you can also get host informations in the same time that service information. This web service provide the same possibility that the service monitoring view.

Using GET method and the URL below:  ::

 api.domain.tld/centreon/api/index.php?object=centreon_realtime_services&action=list

**Header:**

+---------------------+---------------------------------+
|  key                |   value                         |
+=====================+=================================+
| Content-Type        | application/json                |
+---------------------+---------------------------------+
| centreon-auth-token | the value of authToken you got  |
|                     | on the authentication response  |
+---------------------+---------------------------------+

**Parameters**

You can pass a list of parameters in order to select the data you want.

+----------------+--------------------------------------------+
|  Parameters    |   values                                   |
+================+============================================+
| viewType       | select the predefined filter like in the   |
|                | monitoring view: all, unhandled, problems  |
+----------------+--------------------------------------------+
| fields         | the fields list that you want to get       |
|                | separated by a ","                         |
+----------------+--------------------------------------------+
| status         | the status of services that you want to    |
|                | get (ok, warning, critical, unknown,       |
|                | pending, all)                              |
+----------------+--------------------------------------------+
| hostgroup      | hostgroup id filter                        |
+----------------+--------------------------------------------+
| servicegroup   | servicegroup id filter                     |
+----------------+--------------------------------------------+
| instance       | instance id filter                         |
+----------------+--------------------------------------------+
| search         | search pattern applied on service          |
+----------------+--------------------------------------------+
| searchHost     | search pattern applied on host             |
+----------------+--------------------------------------------+
| searchOutput   | search pattern applied on output           |
+----------------+--------------------------------------------+
| criticality    | a specific criticity                       |
+----------------+--------------------------------------------+
| sortType       | the sortType (selected in the field list)  |
+----------------+--------------------------------------------+
| order          | ASC ou DESC                                |
+----------------+--------------------------------------------+
| limit          | number of line you want                    |
+----------------+--------------------------------------------+
| number         | page number                                |
+----------------+--------------------------------------------+

Field list :

+--------------------------+------------------------------------------+
| Fields                   | Description                              |
+==========================+==========================================+
| host_id                  | host id                                  |
+--------------------------+------------------------------------------+
| host_name                | host name                                |
+--------------------------+------------------------------------------+
| host_alias               | host alias (description of the host)     |
+--------------------------+------------------------------------------+
| host_address             | host address (domain name or ip)         |
+--------------------------+------------------------------------------+
| host_state               | host state (UP = 0, DOWN = 2, UNREA = 3) |
+--------------------------+------------------------------------------+
| host_state_type          | host state type (SOFT = 0, HARD = 1)     |
+--------------------------+------------------------------------------+
| host_output              | Plugin output - state message            |
+--------------------------+------------------------------------------+
| host_max_check_attempts  | maximum check attempts for host          |
+--------------------------+------------------------------------------+
| host_check_attempt       | current attempts                         |
+--------------------------+------------------------------------------+
| host_last_check          | last check time                          |
+--------------------------+------------------------------------------+
| host_acknowledged        | acknowledged flag                        |
+--------------------------+------------------------------------------+
| instance                 | name of the instance who check this host |
+--------------------------+------------------------------------------+
| instance_id              | id of the instance who check this host   |
+--------------------------+------------------------------------------+
| host_action_url          | shortcut for action URL                  |
+--------------------------+------------------------------------------+
| host_notes_url           | shortcut for note URL                    |
+--------------------------+------------------------------------------+
| host_notes               | note                                     |
+--------------------------+------------------------------------------+
| description              | service description - service name       |
+--------------------------+------------------------------------------+
| display_name             | service display name                     |
+--------------------------+------------------------------------------+
| service_id               | service id                               |
+--------------------------+------------------------------------------+
| state                    | service state                            |
+--------------------------+------------------------------------------+
| state_type               | service state type (SOFT = 0, HARD = 1)  |
+--------------------------+------------------------------------------+
| output                   | service output returned by plugins       |
+--------------------------+------------------------------------------+
| perfdata                 | service perfdata returned by plugins     |
+--------------------------+------------------------------------------+
| current_attempt          | maximum check attempts for the service   |
+--------------------------+------------------------------------------+
| last_update              | last update date for service             |
+--------------------------+------------------------------------------+
| last_state_change        | last time the state change               |
+--------------------------+------------------------------------------+
| last_hard_state_change   | last time the state change in hard type  |
+--------------------------+------------------------------------------+
| next_check               | next check time for service              |
+--------------------------+------------------------------------------+
| max_check_attempts       | maximum check attempts for service       |
+--------------------------+------------------------------------------+
| action_url               | shortcut for action URL                  |
+--------------------------+------------------------------------------+
| notes_url                | shortcut for note URL                    |
+--------------------------+------------------------------------------+
| notes                    | notes                                    |
+--------------------------+------------------------------------------+
| icone_image              | icone image for service                  |
+--------------------------+------------------------------------------+
| passive_checks           | accept passive results                   |
+--------------------------+------------------------------------------+
| active_checks            | active checks are enabled                |
+--------------------------+------------------------------------------+
| acknowledged             | acknowledged flag                        |
+--------------------------+------------------------------------------+
| notify                   | notification is enabled                  |
+--------------------------+------------------------------------------+
| scheduled_downtime_depth | scheduled_downtime_depth                 |
+--------------------------+------------------------------------------+
| flapping                 | is the host flapping ?                   |
+--------------------------+------------------------------------------+
| event_handler_enabled    | is the event-handfler enabled            |
+--------------------------+------------------------------------------+
| criticality              | criticality fo this service              |
+--------------------------+------------------------------------------+

Example:

Using GET method and the URL below:  ::

  api.domain.tld/centreon/api/index.php?action=list&object=centreon_realtime_services&limit=60&viewType=all&sortType=name&order=desc&fields=id,description,host_id,host_name,state,output

Submit results
==============

You can use the centreon API to submit information to the monitoring engine. All information that you submit will be forwarded to the centreon engine poller that host the configuration.

To provide information, Centreon need to have specific and mandatory information.

The user must be admin or have access to "Reach API Configuration".

For the service submission please provide the following information :

+------------------+------------------------------------------+
| Fields           | Description                              |
+==================+==========================================+
| host             | host name                                |
+------------------+------------------------------------------+
| service          | service description                      |
+------------------+------------------------------------------+
| status           | status id (0, 1, 2, 3)                   |
|                  | or ok, warning, critical, unknown        |
+------------------+------------------------------------------+
| output           | a specific message                       |
+------------------+------------------------------------------+
| perfdata         | all performance metric following the     |
| (optional)       | nagios plugin API                        |
+------------------+------------------------------------------+
| updatetime       | the check time (timestamp)               |
+------------------+------------------------------------------+

For the host submission please provide the following information :

+------------------+------------------------------------------+
| Fields           | Description                              |
+==================+==========================================+
| host             | host name                                |
+------------------+------------------------------------------+
| status           | status id (0, 1, 2, 3)                   |
+------------------+------------------------------------------+
| output           | a specific message                       |
+------------------+------------------------------------------+
| updatetime       | the check time (timestamp)               |
+------------------+------------------------------------------+

To send status, please use the following URL using POST method:  ::

 api.domain.tld/centreon/api/index.php?action=submit&object=centreon_submit_results

**Header**

+---------------------+---------------------------------+
|  key                |   value                         |
|                     |                                 |
+---------------------+---------------------------------+
| Content-Type        | application/json                |
+---------------------+---------------------------------+
| centreon-auth-token | the value of authToken you got  |
|                     | on the authentication response  |
+---------------------+---------------------------------+

**Example of service body submit:**
The body is a json with the parameters provided above formated as below: ::

 {
   "results": [
     {
       "updatetime": "1528884076",
       "host": "Centreon-Central"
       "service": "Memory",
       "status": "2"
       "output": "The service is in CRITICAL state"
       "perfdata": "perf=20"
     },
     {
       "updatetime": "1528884076",
       "host": "Centreon-Central"
       "service": "fake-service",
       "status": "1"
       "output": "The service is in WARNING state"
       "perfdata": "perf=10"
     }
   ]
 }

**Example of body response:** ::
The response body is a json with the HTTP return code and a message for each submit: ::

 {
   "results": [
     {
       "code": 202,
       "message": "The status send to the engine"
     },
     {
       "code": 404,
       "message": "The service is not present."
     }
   ]
 }

-------------
Configuration
-------------

Getting started
===============

Most of the actions available (about 95%) in the command line API is available in the rest API.

Here is an example for listing hosts using rest API.

Using POST method and the URL below:  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi

**Header:**

+---------------------+---------------------------------+
|  key                |   value                         |
|                     |                                 |
+---------------------+---------------------------------+
| Content-Type        | application/json                |
+---------------------+---------------------------------+
| centreon-auth-token | the value of authToken you got  |
|                     | on the authentication response  |
+---------------------+---------------------------------+

**Body:** ::

  {
    "action": "show",
    "object": "HOST"
  }

* The key **action** corresponds to the option **-a** in Centreon CLAPI, the value **show** corresponds to the **-a** option value.
* The key **object** corresponds to the option **-o** in Centreon CLAPI, the value **HOST** corresponds to the **-o** option value.

The equivalent action using Centreon CLAPI is: ::

   [root@centreon ~]# ./centreon -u admin -p centreon -o HOST -a show


**Response:**
The response is a json flow listing all hosts and formated as below: ::

 {
  "result": [
    {
      "id": "12",
      "name": "mail-uranus-frontend",
      "alias": "mail-uranus-frontend",
      "address": "mail-uranus-frontend",
      "activate": "1"
    },
    {
      "id": "13",
      "name": "mail-neptune-frontend",
      "alias": "mail-neptune-frontend",
      "address": "mail-neptune-frontend",
      "activate": "1"
    },
    {
      "id": "14",
      "name": "srvi-mysql01",
      "alias": "srvi-mysql01",
      "address": "srvi-mysql01",
      "activate": "1"
    }
  ]
 }

.. Note:: Some actions need the values key ( the option **-v** in Centreon CLAPI ). Depending on the called action, the body can contain **values** key. We will see that in detail later.

API Calls
=========

All API calls you can do on objects are described below. Note that you need to be authenticate before each call.

API calls on the Host object are fully-detailed below. For the next objects, only the actions available are listed, so just follow the same approach as for the host object for an API call.

Host
====


List hosts
----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "show",
    "object": "host"
  }



**Response** ::

   {
     "result": [
    {
      "id": "79",
      "name": "mail-uranus-frontend",
      "alias": "mail-uranus-frontend",
      "address": "mail-uranus-frontend",
      "activate": "1"
    },
    {
      "id": "80",
      "name": "mail-neptune-frontend",
      "alias": "mail-neptune-frontend",
      "address": "mail-neptune-frontend",
      "activate": "1"
    },
    {
      "id": "81",
      "name": "mail-earth-frontend",
      "alias": "mail-earth-frontend",
      "address": "mail-earth-frontend",
      "activate": "1"
    }
   ]
   }


Add host
--------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "add",
    "object": "host",
    "values": "test;Test host;127.0.0.1;generic-host;central;Linux-SerVers"
  }



**Response** ::

   {
     "result": []
   }


Delete host
-----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "del",
    "object": "host",
    "values": "test"
  }



**Response** ::

   {
     "result": []
   }


Set parameters
--------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setparam",
    "object": "host",
    "values": "test;ParameterToSet;NewParameter"
  }

Available parameters

==================================== =================================================================================
Parameter                            Description
==================================== =================================================================================
2d_coords                            2D coordinates (used by statusmap)

3d_coords                            3D coordinates (used by statusmap)

action_url                           Action URL

activate                             Whether or not host is enabled

active_checks_enabled                Whether or not active checks are enabled

acknowledgement_timeout              Acknowledgement timeout (in seconds)

address                              Host IP Address

alias                                Alias

check_command                        Check command

check_command_arguments              Check command arguments

check_interval                       Normal check interval

check_freshness                      Check freshness (in seconds)

check_period                         Check period

checks_enabled                       Whether or not checks are enabled

contact_additive_inheritance         Enables contact additive inheritance

cg_additive_inheritance              Enables contactgroup additive inheritance

event_handler                        Event handler command

event_handler_arguments              Event handler command arguments

event_handler_enabled                Whether or not event handler is enabled

first_notification_delay             First notification delay (in seconds)

flap_detection_enabled               Whether or not flap detection is enabled

flap_detection_options               Flap detection options

icon_image                           Icon image

icon_image_alt                       Icon image text

max_check_attempts                   Maximum number of attempt before a HARD state is declared

name                                 Host name

normal_check_interval                value in minutes

notes                                Notes

notes_url                            Notes URL

notifications_enabled                Whether or not notification is enabled

notification_interval                Notification interval

notification_options                 Notification options

notification_period                  Notification period

obsess_over_host                     Whether or not obsess over host option is enabled

passive_checks_enabled               Whether or not passive checks are enabled

process_perf_data                    Process performance data command

retain_nonstatus_information         Whether or not there is non-status retention

retain_status_information            Whether or not there is status retention

retry_check_interval                 Retry check interval

snmp_community                       Snmp Community

snmp_version                         Snmp version

stalking_options                     Comma separated options: 'o' for OK, 'd' for Down, 'u' for Unreachable

statusmap_image                      Status map image (used by statusmap

host_notification_options            Notification options (d,u,r,f,s)

timezone                             Timezone
==================================== =================================================================================


**Response** ::

   {
     "result": []
   }


Get parameters
--------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "getparam",
    "object": "host",
    "values": "test;ParameterToGet|ParameterToGet"
  }

Available parameters

==================================== =================================================================================
Parameter                            Description
==================================== =================================================================================
2d_coords                            2D coordinates (used by statusmap)

3d_coords                            3D coordinates (used by statusmap)

action_url                           Action URL

activate                             Whether or not host is enabled

active_checks_enabled                Whether or not active checks are enabled

address                              Host IP Address

alias                                Alias

check_command                        Check command

check_command_arguments              Check command arguments

check_interval                       Normal check interval

check_freshness                      Check freshness (in seconds)

check_period                         Check period

checks_enabled                       Whether or not checks are enabled

contact_additive_inheritance         Enables contact additive inheritance

cg_additive_inheritance              Enables contactgroup additive inheritance

event_handler                        Event handler command

event_handler_arguments              Event handler command arguments

event_handler_enabled                Whether or not event handler is enabled

first_notification_delay             First notification delay (in seconds)

flap_detection_enabled               Whether or not flap detection is enabled

flap_detection_options               Flap detection options

icon_image                           Icon image

icon_image_alt                       Icon image text

max_check_attempts                   Maximum number of attempt before a HARD state is declared

name                                 Host name

normal_check_interval                value in minutes

notes                                Notes

notes_url                            Notes URL

notifications_enabled                Whether or not notification is enabled

notification_interval                Notification interval

notification_options                 Notification options

notification_period                  Notification period

obsess_over_host                     Whether or not obsess over host option is enabled

passive_checks_enabled               Whether or not passive checks are enabled

process_perf_data                    Process performance data command

retain_nonstatus_information         Whether or not there is non-status retention

retain_status_information            Whether or not there is status retention

retry_check_interval                 Retry check interval

snmp_community                       Snmp Community

snmp_version                         Snmp version

stalking_options                     Comma separated options: 'o' for OK, 'd' for Down, 'u' for Unreachable

statusmap_image                      Status map image (used by statusmap

host_notification_options            Notification options (d,u,r,f,s)

timezone                             Timezone
==================================== =================================================================================


**Response** ::

  {
    "result": [{
      "alias": "test",
      "address": "192.168.56.101",
      "timezone": "Europe/Berlin"
    }]
  }


Set instance poller
-------------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setinstance",
    "object": "host",
    "values": "test;Poller-2"
  }



**Response** ::

   {
     "result": []
   }


Get macro
---------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "getmacro",
    "object": "host",
    "values": "mail-uranus-frontend"
  }



**Response**
Here is a response example ::

   {
   "result": [
    {
      "macro name": "ALIVENUM",
      "macro value": "1",
      "is_password": "",
      "description": "",
      "source": "generic-host-bench"
    },
    {
      "macro name": "ALIVEWARNING",
      "macro value": "3000,80",
      "is_password": "",
      "description": "",
      "source": "generic-host-bench"
    },
    {
      "macro name": "ALIVECRITICAL",
      "macro value": "5000,100",
      "is_password": "",
      "description": "",
      "source": "generic-host-bench"
    }
   ]
   }


Set macro
---------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setmacro",
    "object": "host",
    "values": "mail-uranus-frontend;MacroName;NewValue"
  }

To edit an existing custom macro, The MacroName used on the body should be defined on the Custom Marco of the chosen host. If the marco doesn't exist, it will be created.

**Response** ::

 {
  "result": []
 }


Delete macro
------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "delmacro",
    "object": "host",
    "values": "mail-uranus-frontend;MacroName"
  }

The MacroName used on the body is the macro to delete. It should be defined on the Custom Marco of the chosen host.

**Response** ::

 {
  "result": []
 }


Get template
------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "gettemplate",
    "object": "host",
    "values": "mail-uranus-frontend"
  }



**Response**
Here is a response example ::

 {
  "result": [
    {
      "id": "3",
      "name": "Servers-Linux"
    },
    {
      "id": "62",
      "name": "Postfix-frontend"
    },
    {
      "id": "59",
      "name": "Cyrus-murder-frontend"
    }
  ]
  }


Set template
------------


**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "settemplate",
    "object": "host",
    "values": "mail-uranus-frontend;MyHostTemplate"
  }

The MyHostTemplate used on the body should exist as a host template. The new template erase templates already exist.

**Response** ::
  {
  "result": []
  }



Add template
------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "addtemplate",
    "object": "host",
    "values": "mail-uranus-frontend;MyHostTemplate"
  }

The MyHostTemplate used on the body should exist as a host template. The new template is added without erasing template already linked

**Response** ::
  {
  "result": []
  }


Delete template
---------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "deltemplate",
    "object": "host",
    "values": "mail-uranus-frontend;MyHostTemplate"
  }

The MyHostTemplate used on the body should exist as a host template.

**Response** ::
  {
  "result": []
  }


Apply template
--------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "applytpl",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::
  {
  "result": []
  }


Get parent
----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "getparent",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::

 {
  "result": [
    {
      "id": "219",
      "name": "mail-uranus-frontdad"
    }
  ]
 }


Add parent
----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "addparent",
    "object": "host",
    "values": "mail-uranus-frontend;fw-berlin"
  }


**Response** ::

 {
  "result": []
 }

To add more than one parent to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;fw-berlin|fw-dublin"

The add action add the parent without overwriting he previous configuration.

Set parent
----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setparent",
    "object": "host",
    "values": "mail-uranus-frontend;fw-berlin"
  }


**Response** ::

 {
  "result": []
 }

To set more than one parent to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;fw-berlin|fw-dublin"

The set action overwrite the previous configuration before setting the new parent.


Delete parent
-------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "delparent",
    "object": "host",
    "values": "mail-uranus-frontend;fw-berlin"
  }


**Response** ::

 {
  "result": []
 }

To delete more than one parent, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;fw-berlin|fw-dublin"



Get contact group
-----------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "getcontactgroup",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::

  {
  "result": [
    {
      "id": "6",
      "name": "Mail-Operators"
    }
  ]
  }




Add contact group
-----------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "addcontactgroup",
    "object": "host",
    "values": "mail-uranus-frontend;Supervisors"
  }


**Response** ::

 {
  "result": []
 }

To add more than one contactgroup to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;Supervisors|Guest"

The add action add the contact without overwriting he previous configuration.


Set contact group
-----------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setcontactgroup",
    "object": "host",
    "values": "mail-uranus-frontend;Supervisors"
  }


**Response** ::

 {
  "result": []
 }

To set more than one contactgroup to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;Supervisors|Guest"


The set action overwrite the previous configuration before setting the new contactgroup.

Delete contact group
--------------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "delcontactgroup",
    "object": "host",
    "values": "mail-uranus-frontend;Guest"
  }


**Response** ::

 {
  "result": []
 }

To delete more than one contactgroup, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;Guest|Supervisors"


Get contact
-----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "getcontact",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::

  {
  "result": [
    {
      "id": "20",
      "name": "user-mail"
    }
  ]
  }


Add contact
-----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "addcontact",
    "object": "host",
    "values": "mail-uranus-frontend;admin"
  }


**Response** ::

 {
  "result": []
 }

To add more than one contact to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;admin|SuperAdmin"

The add action add the contact without overwriting he previous configuration.


Set contact
-----------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setcontact",
    "object": "host",
    "values": "mail-uranus-frontend;admin"
  }


**Response** ::

 {
  "result": []
 }

To set more than one contact to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;admin|SuperAdmin"


The set action overwrite the previous configuration before setting the new contact.


Delete contact
--------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "delcontact",
    "object": "host",
    "values": "mail-uranus-frontend;Guest"
  }


**Response** ::

 {
  "result": []
 }

To delete more than one contact, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;admin|SuperAdmin"


Get hostgroup
-------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "gethostgroup",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::

 {
  "result": [
    {
      "id": "53",
      "name": "Linux-Servers"
    },
    {
      "id": "63",
      "name": "Mail-Cyrus-Frontend"
    }
  ]
 }

Add hostgroup
-------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "addhostgroup",
    "object": "host",
    "values": "mail-uranus-frontend;Mail-Postfix-Frontend"
  }


**Response** ::

 {
  "result": []
 }

To add more than one hostgroup to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;Mail-Postfix-Frontend|Linux-Servers"

The add action add the hostgroup without overwriting he previous configuration.



Set hostgroup
-------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "sethostgroup",
    "object": "host",
    "values": "mail-uranus-frontend;Linux-Servers"
  }


**Response** ::

 {
  "result": []
 }

To set more than one hostgroup to a host, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;Linux-Servers|Mail-Postfix-Frontend"


The set action overwrite the previous configuration before setting the new hostgroup.


Delete hostgroup
----------------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "delhostgroup",
    "object": "host",
    "values": "mail-uranus-frontend;Linux-Servers"
  }


**Response** ::

 {
  "result": []
 }

To delete more than one hostgroup, use the character '|'. Ex:  ::

  "values": "mail-uranus-frontend;Linux-Servers|Mail-Postfix-Frontend"


Enable
------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "enable",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::

 {
  "result": []
 }



Disable
-------

**POST**  ::

 api.domain.tld/centreon/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon-auth-token | the value of authToken you got                 |
|                     | on the response of the authentication part     |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "disable",
    "object": "host",
    "values": "mail-uranus-frontend"
  }


**Response** ::

 {
  "result": []
 }


ACL
===

**Object**
 * ACL

**Actions**

 * reload
 * lastreload


Action ACL
----------

**Object**
 * ACLACTION

**Actions**

 * show
 * add
 * del
 * setparam
 * getaclgroup
 * grant
 * revoke

ACL groups
----------

**Object**
 * ACLGROUP

**Actions**

 * show
 * add
 * del
 * setparam
 * getmenu
 * getaction
 * getresource
 * getcontact
 * getcontactgroup
 * setmenu
 * setaction
 * setresource
 * addmenu
 * addaction
 * addresource
 * delmenu
 * delaction
 * delresource
 * setcontact
 * setcontactgroup
 * addcontact
 * addcontactgroup
 * delcontact
 * delcontactgroup


Menu ACL
--------

**Object**
 * ACLMENU

**Actions**

 * show
 * add
 * del
 * setparam
 * getaclgroup
 * grant
 * revoke


Resource ACL
------------

**Object**
 * ACLRESOURCE

**Actions**

 * show
 * add
 * del
 * setparam
 * getaclgroup
 * grant
 * revoke

Centreon Broker
===============

**Object**
 * CENTBROKERCFG

**Actions**

 * show
 * add
 * del
 * setparam
 * listinput, listoutput, listlogger, listcorrelation, listtemporary, liststats
 * getinput , getoutput, getlogger, getcorrelation, gettemporary, getstats
 * addinput, addoutput, addlogger, addcorrelation, addtemporary, addstats
 * delinput, deloutput, dellogger, delcorrelation, deltemporary, delstats
 * setinput, setoutput, setlogger, setcorrelation, settemporary, setstats


CGI CFG
=======

**Object**
 * CGICFG

**Actions**

 * show
 * add
 * del
 * setparam


Commands
========

**Object**
 * CMD

**Actions**

 * show
 * add
 * del
 * setparam

Contacts
========

**Object**
 * CONTACT

**Actions**

 * show
 * add
 * del
 * setparam
 * enable
 * disable

Contact templates
-----------------

**Object**
 * CONTACTTPL

**Actions**

 * show
 * add
 * del
 * setparam
 * enable
 * disable


Contact groups
--------------

**Object**
 * CG

**Actions**

 * show
 * add
 * del
 * setparam
 * enable
 * disable
 * getcontact
 * addcontact
 * setcontact
 * delcontact


Dependencies
============

**Object**
 * DEP

**Actions**

 * show
 * add
 * del
 * setparam
 * listdep
 * addparent
 * addchild
 * delparent
 * delchild


Downtimes
=========

**Object**
 * DOWNTIME

**Actions**

 * show
 * add
 * del
 * listperiods
 * addweeklyperiod
 * addmonthlyperiod
 * addspecificperiod
 * addhost, addhostgroup, addservice, addservicegroup
 * delhost, delhostgroup, delservice, delservicegroup
 * sethost, sethostgroup, setservice, setservicegroup

Host template
=============

**Object**
 * HTPL

**Actions**
APPLYTPL and SETINSTANCE actions on HTPL

 * show
 * add
 * del
 * setparam
 * getmacro
 * setmacro
 * delmacro
 * getparent
 * addparent
 * setparent
 * delparent
 * getcontactgroup
 * addcontactgroup
 * setcontactgroup
 * delcontactgroup
 * getcontact
 * addcontact
 * setcontact
 * delcontact
 * gethostgroup
 * addhostgroup
 * sethostgroup
 * delhostgroup
 * setseverity
 * unsetseverity
 * enable
 * disable

Host categories
===============

**Object**
 * HC

**Actions**

 * show
 * add
 * del
 * getmember
 * addmember
 * setmember
 * setseverity
 * unsetseverity
 * delmember


Hostgroups
==========

**Object**
 * HG

**Actions**

 * show
 * add
 * del
 * setparam
 * getmember
 * addmember
 * setmember
 * delmember



Instances ( Pollers)
====================

**Object**
 * INSTANCE

**Actions**

 * show
 * add
 * del
 * setparam
 * gethosts


Service templates
=================

**Object**
 * STPL

**Actions**

 * show
 * add
 * del
 * setparam
 * addhosttemplate
 * sethosttemplate
 * delhosttemplate
 * getmacro
 * setmacro
 * delmacro
 * getcontact
 * addcontact
 * setcontact
 * delcontact
 * getcontactgroup
 * setcontactgroup
 * delcontactgroup
 * gettrap
 * settrap
 * deltrap


Services
========

**Object**
 * SERVICE

**Actions**

 * show
 * add
 * del
 * setparam
 * addhost
 * sethost
 * delhost
 * getmacro
 * setmacro
 * delmacro
 * setseverity
 * unsetseverity
 * getcontact
 * addcontact
 * setcontact
 * delcontact
 * getcontactgroup
 * setcontactgroup
 * delcontactgroup
 * gettrap
 * settrap
 * deltrap


Service groups
==============

**Object**
 * SG

**Actions**

 * show
 * add
 * del
 * setparam
 * getservice
 * gethostgroupservice
 * addservice
 * setservice
 * addhostgroupservice
 * sethostgroupservice
 * delservice
 * delhostgroupservice


Service categories
==================

**Object**
 * SC

**Actions**

 * show
 * add
 * del
 * setparam
 * getservice
 * getservicetemplate
 * addservice
 * setservice
 * addservicetemplate
 * setservicetemplate
 * delservice
 * delservicetemplate
 * setseverity
 * unsetseverity

Time periods
============

**Object**
 * TIMEPERIOD

**Actions**

 * show
 * add
 * del
 * setparam
 * getexception
 * setexception
 * delexception


Traps
=====

**Object**
 * TRAP

**Actions**

 * show
 * add
 * del
 * setparam
 * getmatching
 * addmatching
 * delmatching
 * updatematching


Vendors
-------

**Object**
 * VENDOR

**Actions**

 * show
 * add
 * del
 * setparam
 * generatetraps

-----------
Code errors
-----------



+---------------------------+---------------------------------------------------+
| **Code**                  |  **Messages**                                     |
+---------------------------+---------------------------------------------------+
|  200                      | Successful                                        |
+---------------------------+---------------------------------------------------+
|  400                      | * Missing parameter                               |
|                           | * Missing name parameter                          |
|                           | * Unknown parameter                               |
|                           | * Objects are not linked                          |
+---------------------------+---------------------------------------------------+
|  401                      | Unauthorized                                      |
+---------------------------+---------------------------------------------------+
|  404                      | * Object not found                                |
|                           | * Method not implemented into Centreon API        |
+---------------------------+---------------------------------------------------+
|  409                      | * Object already exists                           |
|                           | * Name is already in use                          |
|                           | * Objects already linked                          |
+---------------------------+---------------------------------------------------+
|  500                      | Internal server error (custom message)            |
+---------------------------+---------------------------------------------------+
