==========
API Rest
==========



Introduction
------------

Welcome to the Centreon API rest documentation. This documentation is for devlopers familiar with HTTP requests and JSON. It  explains various API operations, related request and response structure, and error code.
If you are not familiar with the JSON API, we recommand you to use the Centreon command line API documentation which suits better on your needs.

This documentation is available only in english. 


Permissions
-----------

To do API calls from a specific Centreon user, you need to have permission on the API.
You have to edit user settings on the menu **Configuration > Users > Contacts/Users**,
edit user and on second tab check box **Reach API**.


Authentification
----------------

Using POST methode and the URL below: ::

 api.domain.tld/api/index.php?action=authenticate

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

The response is a json flow getting back the authentification token  ::

  {
  "authToken": "NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc="
  }

This token will be used later on the other API actions.


Getting started
----------------

95% of actions you can do using Centreon command line API are available with the API rest.

Here is an axample about listing hosts using rest API.

Using POST methode and the URL below:  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi

**Header:**

+---------------------+---------------------------------+
|  key                |   value                         |
|                     |                                 |
+---------------------+---------------------------------+
| Content-Type        | application/json                |
+---------------------+---------------------------------+
| centreon-auth-token | the value of authToken you got  |
|                     | on the authentification response|
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
----------

All API calls you can do on objects are desribed below. Note that you need to be authentify before each call. 

API calls on the Host object are fully-detailed below. For the next objets, only the actions available are listed, so just follow the same approach as for the host object for an API call.

Host
~~~~


List hosts
##########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
###########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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

vrml_image                           VRML image

host_notification_options            Notification options (d,u,r,f,s)

timezone                             Timezone
==================================== =================================================================================


**Response** ::

   {
     "result": []
   }


Set instance poller
####################

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "setmacro",
    "object": "host",
    "values": "mail-uranus-frontend;MacroName;NewValue"
  }

To edit an existing custom marco, The MacroName used on the body should be defined on the Custom Marco of the choosen host. If the marco doesn't exist, it will be created.

**Response** ::
 
 {
  "result": []
 }


Delete macro
#############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
+---------------------+------------------------------------------------+


**Body**  ::

  {
    "action": "delmacro",
    "object": "host",
    "values": "mail-uranus-frontend;MacroName"
  }

The MacroName used on the body is the macro to delete. It should be defined on the Custom Marco of the choosen host. 

**Response** ::

 {
  "result": []
 }


Get template
############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
############


**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
###############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#################

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#################

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#################

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
####################

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
###########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
###########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
###########

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
##############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#############

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
################

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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


Set severity
############

Coming soon

Unset severity
##############

Coming soon

Enable
######

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
#######

**POST**  ::

 api.domain.tld/api/index.php?action=action&object=centreon_clapi


**Header**

+---------------------+------------------------------------------------+
|  key                |   value                                        |
|                     |                                                |
+---------------------+------------------------------------------------+
| Content-Type        | application/json                               |
+---------------------+------------------------------------------------+
| centreon_auth_token | the value of authToken you got                 |
|                     | on the response of the authentification part   |
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
~~~~~

**Object**
 * ACL

**Actions**

 * reload
 * lastreload


Action ACL
~~~~~~~~~~

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
~~~~~~~~~~

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
~~~~~~~~~

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
~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~

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
~~~~~~~

**Object**
 * CGICFG

**Actions**

 * show
 * add
 * del
 * setparam


Commands
~~~~~~~~

**Object**
 * CMD

**Actions**

 * show
 * add
 * del
 * setparam

Contacts
~~~~~~~~

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
~~~~~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~

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
~~~~~~~~~~

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
~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~~

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
~~~~~~~~~~

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
~~~~~~~~~~~~~~~~~~~~~

**Object**
 * INSTANCE

**Actions**

 * show
 * add
 * del
 * setparam
 * gethosts
 

Service templates
~~~~~~~~~~~~~~~~~

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
~~~~~~~~~

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
~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~~~~~

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
~~~~~~~~~~~~

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
~~~~~~~~~~~

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
~~~~~~~~

**Object**
 * VENDOR

**Actions**

 * show
 * add
 * del
 * setparam
 * generatetraps


Code errors
------------



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
