Command object
^^^^^^^^^^^^^^

.. http:get:: /api/centreon-configuration/command

   Get the list of commands

   **Example request**

   .. sourcecode:: http

      GET /api/centreon-configuration/command
      Host: centreon_host
      Accept: application/json, text/javascript
      centreon-x-token: token_from_authentication

   **Example response**

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Vary: Accept
      Content-Type: application/json

      {
        "command":
          [
            {
              "id": "1",
              "name": "check_host_alive",
              "command_line": "$USER1$/check_icmp -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1",
              "type": "2",
              "href": "http://centreon_host/centreon-configuration/command/1"
            },
            {
              "id": "2",
              "name": "check_eth0",
              "command_line": "$USER1$/check_centreon_snmp_traffic -H localhost -i eth0 -n",
              "type": "2",
              "href": "http://centreon_host/centreon-configuration/command/2"
            }
          ],
          links: []
      }

   :query count: The number of returned object. default is undefined
   :query offset: Offset number. default is 0

   :reqheader Accept: the response content type depends on :http:header:`Accept` header
   :reqheader X-centreon-x-token: The authentication token, the correct name of the header is **centreon-x-token**
   :resheader Content-Type: this depends on :http:header:`Accept` header of request

   :statuscode 200: no error

.. http:post:: /api/centreon-configuration/command

   Create a new command

   **Example request**

   .. sourcecode:: http

      POST /api/centreon-configuration/command
      Host: centreon_host
      Accept: application/json, text/javascript
      centreon-x-token: token_from_authentication

   **Example response**

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Vary: Accept
      Content-Type: application/json

   :reqjson object data: The data for create object
   :reqjson string data/type: The object type : command
   :reqjson object data/attributes: The list of attributes for create the new command
   :reqjson string data/attributes/command_name: The name of the command (required)
   :reqjson integer data/attributes/command_type: The command type (required) 1 Notification, 2 Command, 3 Misc, 4 Discovery
   :reqjson string data/attributes/command_line: The program with arguments to execute, see the Centreon documentation for list of macros (required)
   :reqjson integer data/attributes/enable_shell: If a shell is executed when Centreon Engine run the command
   :reqjson integer data/attributes/connectod_id: The id of connector if it's use
   :reqjson string data/attributes/command_comment: A comment for the command

   :reqheader Accept: the response content type depends on :http:header:`Accept` header
   :reqheader X-centreon-x-token: The authentication token, the correct name of the header is **centreon-x-token**
   :resheader Content-Type: this depends on :http:header:`Accept` header of request

   :statuscode 200: no error
