Feature:
    In order to platforms
    As a user
    I want to register a platform to an other

    Background:
        Given a running instance of Centreon Web API
    #    And the endpoints are described in Centreon Web API documentation

    Scenario: register a poller
        Given I am logged in
        # trying to register a server
        # Should be successful
        When I send a POST request to '/latest/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.2",
        "parent_address": "1.1.1.1"
    }
    """
        Then the response code should be "201"

        # trying to register a server using type non formatted as expected.
        # Should be successful
        When I send a POST request to '/latest/platform/topology' with body:
    """
    {
        "name": "my poller 2",
        "type": "pOlLEr",
        "address": "1.1.1.3",
        "parent_address": "1.1.1.1"
    }
    """
        Then the response code should be "201"

        # trying to insert already registered server.
        # Should fail and an error should be returned
        When I send a POST request to '/latest/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.2"
        "parent_address": 1.1.1.1"
    }
    """
        Then the response code should be "409"

        # trying to insert a platform which parent address is missing
        # Should fail and an error should be returned
        When I send a POST request to '/latest/platform/topology' with body:
    """
    {
        "name": "my poller 3",
        "type": "Poller3",
        "address": "1.1.1.4"
    }
    """
        Then the response code should be "409"
