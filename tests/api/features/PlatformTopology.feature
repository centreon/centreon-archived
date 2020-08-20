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
        # (Notice : this step is automatically executed on a real platform on fresh install and update)
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
            {"code":409,"message":"The Central type server : 'wrong_name'@'1.1.1.10' does not match the one configured in Centreon"
            """

        # Successfully register the Central on the container
        # (Notice : this step is automatically executed on a real platform on fresh install and update)
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "Central",
                "type": "central",
                "address": "1.1.1.10"
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
            {"code":409,"message":"A Central : 'Central'@'1.1.1.10' is already registered"}
            """

        # Register a second Central while the first is still registered / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "Central2",
                "type": "central",
                "address": "1.1.1.11"
            }
            """
        Then the response code should be "409"
        And the response should be equal to:
            """
            {"code":409,"message":"A Central : 'Central'@'1.1.1.10' is already registered"}
            """


        # Register a poller linked to the Central.
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller",
                "type": "poller",
                "address": "1.1.1.1",
                "parent_address": "1.1.1.10"
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
            {"code":409,"message":"A platform using the name : 'my poller' or address : '1.1.1.1' already exists"}
            """

        # Register a poller using type not formatted as expected / Should be successful
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller 2",
                "type": "pOlLEr",
                "address": "1.1.1.2",
                "parent_address": "1.1.1.10"
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
                "parent_address": "6.6.6.6"
            }
            """
        Then the response code should be "500"
        And the response should be equal to:
            """
            {"code":500,"message":"No parent platform was found for : 'my poller 3'@'1.1.1.3'"}
            """

        # Register a poller with no parent address / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
            """
            {
                "name": "my poller 4",
                "type": "poller",
                "address": "1.1.1.4",
            }
            """
        Then the response code should be "500"
        And the response should be equal to:
            """
            {"code":500,"message":"Missing mandatory parent address, to link the platform : 'my poller 4'@'1.1.1.4'"}
            """