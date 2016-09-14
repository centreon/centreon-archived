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

For the authentication follow the endpoint below:

.. http:post:: /api/index.php?action=authenticate

  Authenticate a Centreon user to the REST API
  
  **Example request**
  
  .. sourcecode:: http
  
    POST /api/index.php?action=authenticate
    Host: api.domain.tld
    Accept: application/json
    Body:
      {
        "username": "admin",
        "password": "centreon"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "authToken": "NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc="
    }
    
  :query action: Must be authenticate, define the action to execute
  :<json string username: The Centreon user login
  :<json string password: The Centreon user password
  :>json string authToken: The authentication token, this token will be used for call API
  :statuscode 200: Authentify
  :statuscode 400: Bad parameters
  :statuscode 401: Account not enabled, the Centreon user cannot use the REST API
  :statuscode 403: Bad credentials


Getting started
----------------

95% of actions you can do using Centreon command line API are available with the API rest.

Description of endpoint
#######################

The endpoint for call clapi action is described below:

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Call a Centreon CLAPI action
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "show",
        "object": "HOST"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
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
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: The CLAPI action, option -a of CLAPI
  :<json string object: The CLAPI object, option -o of CLAPI
  :<json string [values]: The CLAPI values, option -v of CLAPI
  :>json array result: The list of result
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)  

Examples
########

Here is an axample about listing hosts using rest API.

Using POST methode and the URL below:  ::

 http://api.domain.tld/api/index.php?action=action&object=centreon_clapi

**Header:**

+---------------------+---------------------------------+
|  key                |   value                         |
|                     |                                 |
+---------------------+---------------------------------+
| Content-Type        | application/json                |
+---------------------+---------------------------------+
| centreon_auth_token | the value of authToken you got  |
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

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  List hosts
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "show",
        "object": "HOST"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
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
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: show, action for listing
  :<json string object: HOST, object host
  :>json array result: The list of result
  :>json number result.id: The host id
  :>json string result.name: The host name
  :>json string result.alias: The host alias
  :>json string result.address: The host address, IP address or dns name
  :>json number result.activate: 0 or 1, if the host is activated
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Add host
##########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Add a hosts
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "add",
        "object": "HOST",
        "values": "test;Test host;127.0.0.1;generic-host;central;Linux-SerVers"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: add, action for add
  :<json string object: HOST, object host
  :<json string values: The list of information for create a host
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Delete host
###########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Delete a hosts
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "show",
        "object": "HOST",
        "values": "test"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: del, action for delete
  :<json string object: HOST, object host
  :<json string values: The host name of the host to delete
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)



Set parameters
##############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a paramater to a hosts
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "setparam",
        "object": "HOST",
        "values": "test;ParameterToSet;NewParameter"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: setparam, action for set of paramater
  :<json string object: HOST, object host
  :<json string values: The host name of the host to update, the parameter key and the parameter value
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Available parameters is described in :ref:`CLAPI setparam section <clapi-hosts-setparam>`.


Set instance poller
####################

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Link a host to an instance
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "setinstance",
        "object": "HOST",
        "values": "test;Poller2"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: setinstance, action for set an instance
  :<json string object: HOST, object host
  :<json string values: The host name of the host to link and the poller name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Get macro 
##########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Get the list of macros for a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "getmacro",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
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
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: getmacro, action for get list of macro
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of macros
  :>json string result.macro name: The macro name
  :>json string result.macro value: The macro value
  :>json number result.is_password: If the macro is a password
  :>json string result.description: The macro description
  :>json string result.source: The hostname or the host template name where the macro is defined
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set macro
#########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a macro to a host
  
  To edit an existing custom macro, The MacroName used on the body should be defined on the Custom Marco of the choosen host. If the marco doesn’t exist, it will be created.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "setmacro",
        "object": "HOST",
        "values": "mail-uranus-frontend;MacroName;NewValue"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: setmacro, action for set a macro
  :<json string object: HOST, object host
  :<json string values: The host name of the host, the macro name and the macro value
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Delete macro
#############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Delete a macro to a host
  
  The MacroName used on the body is the macro to delete. It should be defined on the Custom Marco of the choosen host.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "delmacro",
        "object": "HOST",
        "values": "mail-uranus-frontend;MacroName"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: delmacro, action for delete a macro
  :<json string object: HOST, object host
  :<json string values: The host name of the host and the macro name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Get template
############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Get the list of template for a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "gettemplate",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
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
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :<json string action: gettemplate, action for get the list of host template
  :reqheader centreon_auth_token: The authentication token
  :<json string object: HOST, object host
  :<json string values: The host name of the host
  :>json array result: The list of result
  :>json number result.id: The host template id
  :>json string result.name: The host template name
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set template
############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a host template to a host
  
  The MyHostTemplate used on the body should exist as a host template. The new template erase templates already exist.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "settemplate",
        "object": "HOST",
        "values": "mail-uranus-frontend;MyHostTemplate"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: settemplate, action for set a host template
  :<json string object: HOST, object host
  :<json string values: The host name of the host and the host template name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Add template
############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Add a host template to a host
  
  The MyHostTemplate used on the body should exist as a host template. The new template is added without erasing template already linked.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "addtemplate",
        "object": "HOST",
        "values": "mail-uranus-frontend;MyHostTemplate"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: addtemplate, action for add a host template
  :<json string object: HOST, object host
  :<json string values: The host name of the host and the host template name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Delete template
###############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Unlink a host to a host template.
  
  The MyHostTemplate used on the body should exist as a host template.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "deltemplate",
        "object": "HOST",
        "values": "mail-uranus-frontend;MyHostTemplate"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: deltemplate, action for unlink a host template
  :<json string object: HOST, object host
  :<json string values: The host name of the host and the host template name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Apply template
##############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Apply template to host, generate services link to host template
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "applytpl",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: applytpl, action for apply host template to the host
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Get parent
##########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Get the host parent
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "getparent",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": [
        {
          "id": "219",
          "name": "mail-uranus-frontdad"
        }
      ]
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: getparent, action for get the host parent
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result, this array is empty
  :>json number result.id: The host parent id
  :>json string result.name: The host parent name
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message) 

Add parent
##########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Add a parent to a host
  
  The add action add the parent without overwriting he previous configuration.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "addparent",
        "object": "HOST",
        "values": "mail-uranus-frontend;fw-berlin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: addparent, action for add a host parent to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the host parent name. To add more than one parent to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set parent
##########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a parent to a host
  
  The set action overwrite the previous configuration before setting the new parent.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "addparent",
        "object": "HOST",
        "values": "mail-uranus-frontend;fw-berlin|fw-dublin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: setparent, action for set a host parent to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the host parent name. To set more than one parent to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Delete parent
#############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Delete a parent to a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "delparent",
        "object": "HOST",
        "values": "mail-uranus-frontend;fw-berlin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: delparent, action for delete a host parent to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the host parent name. To delete more than one parent to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Get contact group
#################

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Get the list of contactgroup for a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "getcontactgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": [
        {
          "id": "6",
          "name": "Mail-Operators"
        }
      ]
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: getcontactgroup, action for get the list of contact group from the host
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result
  :>json number id: The contact group id
  :>json string name: The contact group name
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Add contact group
#################

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Add a contact group to a host
  
  The add action add the contact without overwriting he previous configuration.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "addcontactgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend;Supervisors"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: addcontactgroup, action for add a contact group to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the contact group name. To add more than one contact group to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set contact group
#################

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a contact group to a host
  
  The set action overwrite the previous configuration before setting the new contactgroup.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "setcontactgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend;Supervisors|Guest"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: setcontactgroup, action for set a contact group to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the contact group name. To set more than one contact group to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Delete contact group
####################

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Delete a contact group to a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "delcontactgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend;fw-berlin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: delcontactgroup, action for delete a contact group to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the contact group name. To delete more than one contact group to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Get contact
###########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Get the list of contact for a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "getcontact",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": [
        {
          "id": "20",
          "name": "user-mail"
        }
      ]
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: getcontact, action for get the list of contact from the host
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result
  :>json number id: The contact id
  :>json string name: The contact name
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Add contact
###########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Add a contact to a host
  
  The add action add the contact without overwriting he previous configuration.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "addcontact",
        "object": "HOST",
        "values": "mail-uranus-frontend;admin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: addcontact, action for add a contact to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the contact name. To add more than one contact to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set contact
###########

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a contact to a host
  
  The set action overwrite the previous configuration before setting the new contact.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "setcontact",
        "object": "HOST",
        "values": "mail-uranus-frontend;admin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: setcontact, action for set a contact to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the contact name. To set more than one contact to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Delete contact
##############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Delete a contact to a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "delcontact",
        "object": "HOST",
        "values": "mail-uranus-frontend;admin"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: delcontact, action for delete a contact to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the contact name. To delete more than one contact to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Get hostgroup
##############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Get the list of host group for a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "gethostgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
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
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: getcontact, action for get the list of host groups from the host
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result
  :>json number id: The host group id
  :>json string name: The host group name
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Add hostgroup
##############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Add a host group to a host
  
  The add action add the host group without overwriting he previous configuration.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "addhostgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend;Mail-Postfix-Frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: addhostgroup, action for add a host group to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the host group name. To add more than one host group to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set hostgroup
#############

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Set a host group to a host
  
  The set action overwrite the previous configuration before setting the new host group.
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "sethostgroup",
        "object": "HOST",
        "values": "mail-uranus-frontend;Mail-Postfix-Frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: sethostgroup, action for set a host group to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the host group name. To set more than one host group to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Delete hostgroup
################

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Delete a host group to a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "delcontact",
        "object": "HOST",
        "values": "mail-uranus-frontend;Linux-Servers|Mail-Postfix-Frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: delhostgroup, action for delete a host group to the host
  :<json string object: HOST, object host
  :<json string values: The host name and the host group name. To delete more than one host group to a host, use the character '|'
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)

Set severity
############

Coming soon

Unset severity
##############

Coming soon

Enable
######

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Enable a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "enable",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: enable, action for enable a host
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)


Disable
#######

.. http:post:: /api/index.php?action=action&object=centreon_clapi

  Disable a host
  
  **Example request**
  
  .. sourcecode:: http
    
    POST /api/index.php?action=action&object=centreon_clapi
    Host: api.domain.tld
    Accept: application/json
    centreon_auth_token: NTc1MDU3MGE3M2JiODIuMjA4OTA2OTc=
    Body:
      {
        "action": "disable",
        "object": "HOST",
        "values": "mail-uranus-frontend"
      }
      
  **Example response**
  
  .. sourcecode:: http
  
    HTTP/1.1 200 Ok
    Vary: Accept
    Content-Type: application/json
    {
      "result": []
    }
    
  :query action: Must be action, define the action to execute
  :query object: Must be centreon_clapi, the object to use for the action
  :reqheader centreon_auth_token: The authentication token
  :<json string action: disable, action for disable a host
  :<json string object: HOST, object host
  :<json string values: The host name
  :>json array result: The list of result, this array is empty
  :statuscode 200: Successful
  :statuscode 400: Missing parameter
  :statuscode 400: Missing name parameter
  :statuscode 400: Unknown parameter
  :statuscode 400: Objects are not linked
  :statuscode 401: Unauthorized
  :statuscode 404: Object not found
  :statuscode 404: Method not implemented into Centreon API
  :statuscode 409: Object already exists
  :statuscode 409: Name is already in use
  :statuscode 409: Objects already linked
  :statuscode 500: Internal server error (custom message)
  
  
You can find the all list of objects and actions on :ref:`CLAPI documentation <clapi-documentation>`.

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