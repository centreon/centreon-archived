Feature:
    In order to platforms
    As a user
    I want to register a platform to the Central server

    Background:
        Given a running instance of Centreon Web API
    #    And the endpoints are described in Centreon Web API documentation

    Scenario: register a poller
        Given I am logged in
        When I send a POST request to '/beta/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.1"
        "parent_address": "1.1.1.10"
    }
    """
        Then the response code should be "201"

        # trying to register a server using type not formatted as expected.
        # Should be successful
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

        # trying to insert already registered server.
        # Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.1"
        "parent_address": "1.1.1.10"
    }
    """
        Then the response code should be "409"
