Feature:
    In order to set the platforms' topology
    As a user
    I want to register a platform to the Central server

    Background:
        Given a running instance of Centreon Web API
    #   And the endpoints are described in Centreon Web API documentation

    Scenario: Register servers in Platform Topology
        Given I am logged in

        # Register the Central on the container with a name which doesn't exist in nagios_server table
        # Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "wrong_name",
                "type": "central",
                "address": "1.1.1.10"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"message":"The server type 'central' : 'wrong_name'@'1.1.1.10' does not match the one configured in Centreon or is disabled"}
            """

        # Successfully register the Central on the container
        # (Notice : this step is automatically done on a real platform on fresh install and update)
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "Central",
                "type": "central",
                "address": "1.1.1.10",
                "hostname": "central.test.localhost.localdomain"
            }
            """
        Then the response code should be "201"

        # Register the same Central a second time / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "Central",
                "type": "central",
                "address": "1.1.1.10"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"message":"A 'central': 'Central'@'1.1.1.10' is already registered"}
            """

        # Register a second Central while the first is still registered / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "Central2",
                "type": "central",
                "address": "1.1.1.11",
                "hostname": "server.test.localhost.localdomain"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"message":"A 'central': 'Central'@'1.1.1.10' is already registered"}
            """

        # Register a Central linked to another Central
        # Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "Central_2",
                "type": "central",
                "address": "1.1.1.11",
                "parent_address": "1.1.1.10"
            }
            """
        Then the response code should be "400"
        And the response should be equal to:
            """
            {"message":"Cannot use parent address on a Central server type"}
            """

        # Check data consistency
        # Register a platform using not allowed type / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "wrong_type_server",
                "type": "server",
                "address": "6.6.6.1",
                "parent_address": "1.1.1.10",
                "hostname": "server.test.localhost.localdomain"
            }
            """
        Then the response code should be "400"
        And the response should be equal to:
            """
            {"message":"The platform type of 'wrong_type_server'@'6.6.6.1' is not consistent"}
            """

        # Register a platform using inconsistent address / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "inconsistent_address",
                "type": "poller",
                "address": "666.",
                "parent_address": "1.1.1.10"
            }
            """
        Then the response code should be "400"
        And the response should be equal to:
            """
            {"message":"The address '666.' of 'inconsistent_address' is not valid or not resolvable"}
            """

        # Register a platform using name with at least one space / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "illegal space found",
                "type": "poller",
                "address": "666.",
                "parent_address": "1.1.1.10"
            }
            """
        Then the response code should be "400"
        # Using the string '~!$&*"|'<>?,()=' in the returned message. We cannot test the response message.
        # as it contains unescaped characters and behat interpret them, so the string are always different.

        # Register a platform using name with illegal characters / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "ill*ga|_character$_found",
                "type": "poller",
                "address": "666.",
                "parent_address": "1.1.1.10"
            }
            """
        Then the response code should be "400"
        # Using the string '~!$&*"|'<>?,()=' in the returned message. We cannot test the response message.
        # as it contains unescaped characters and behat interpret them, so the string are always different.

        # Register a platform using hostname with at least one space / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "space_in_hostname",
                "type": "poller",
                "address": "666.",
                "parent_address": "1.1.1.10",
                "hostname": "found.space_in.host name.wrong"
            }
            """
        Then the response code should be "400"
        # Using the string '~!$&*"|'<>?,()=' in the returned message. We cannot test the response message.
        # as it contains unescaped characters and behat interpret them, so the string are always different.

        # Register a platform using hostname with illegal characters / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "illegal_character_in_hostname",
                "type": "poller",
                "address": "666.",
                "parent_address": "1.1.1.10",
                "hostname": "found.i|legal.char*cter_!n.hostname"
            }
            """
        Then the response code should be "400"
        # Using the string '~!$&*"|'<>?,()=' in the returned message. We cannot test the response message.
        # as it contains unescaped characters and behat interpret them, so the string are always different.

        # Register a platform using inconsistent parent_address / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "inconsistent parent address",
                "type": "poller",
                "address": "6.6.6.1",
                "parent_address": "666.",
                "hostname": "poller.test.localhost.localdomain"
            }
            """
        Then the response code should be "400"
        And the response should be equal to:
            """
            {"message":"The address '666.' of 'inconsistent parent address' is not valid or not resolvable"}
            """

        # Register a poller linked to the Central.
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller",
                "type": "poller",
                "address": "1.1.1.1",
                "parent_address": "1.1.1.10",
                "hostname": "poller.test.localhost.localdomain"
            }
            """
        Then the response code should be "201"

        # Register a second time the already registered poller / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller",
                "type": "poller",
                "address": "1.1.1.1",
                "parent_address": "1.1.1.10"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"message":"A platform using the name : 'my poller' or address : '1.1.1.1' already exists"}
            """

        # Register a poller using type not formatted as expected / Should be successful
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller 2",
                "type": "pOlLEr",
                "address": "1.1.1.2",
                "parent_address": "1.1.1.10",
                "hostname": "poller2.test.localhost.localdomain"
            }
            """
        Then the response code should be "201"

        # Register a poller with not registered parent / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller 3",
                "type": "poller",
                "address": "1.1.1.3",
                "parent_address": "6.6.6.6",
                "hostname": "poller.test.localhost.localdomain"
            }
            """
        Then the response code should be "404"
        And the response should be equal to:
            """
            {"message":"No parent platform was found for : 'my poller 3'@'1.1.1.3'"}
            """

        # Register a poller with no parent address / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller 4",
                "type": "poller",
                "address": "1.1.1.4"
            }
            """
        Then the response code should be "404"
        And the response should be equal to:
            """
            {"message":"Missing mandatory parent address, to link the platform : 'my poller 4'@'1.1.1.4'"}
            """

        # Register a poller using same address and parent address / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller 4",
                "type": "poller",
                "address": "1.1.1.4",
                "parent_address": "1.1.1.4"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"message":"Same address and parent_address for platform : 'my poller 4'@'1.1.1.4'."}
            """

        # Register a platform behind wrong parent type / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "inconsistent parent type",
                "type": "poller",
                "address": "6.6.6.1",
                "parent_address": "1.1.1.2"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"message":"Cannot register the 'poller' platform : 'inconsistent parent type'@'6.6.6.1' behind a 'poller' platform"}
            """
