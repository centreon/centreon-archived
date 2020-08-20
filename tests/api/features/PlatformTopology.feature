Feature:
    In order to platforms
    As a user
    I want to register a platform to the Central server

    Background:
        Given a running instance of Centreon Web API
        And the endpoints are described in Centreon Web API documentation

    Scenario: register a poller
        Given I am logged in
        # Register the Central on the container (this step is already executed on a real platform)
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

        # Register a poller linked to the Central.
        When I send a POST request to '/beta/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.1",
        "parent_address": "1.1.1.10"
    }
    """
        Then the response code should be "201"

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

        # Register a poller already registered / Should fail and an error should be returned
        When I send a POST request to '/beta/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.1",
        "parent_address": "1.1.1.10"
    }
    """
        Then the response code should be "409"
