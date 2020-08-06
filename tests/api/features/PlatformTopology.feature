Feature:
    In order to platforms
    As a user
    I want to register a platform to an other

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
    }
    """
        Then the response code should be "201"

        When I send a POST request to '/beta/platform/topology' with body:
    """
    {
        "name": "my poller",
        "type": "Poller",
        "address": "1.1.1.1"
    }
    """
        Then the response code should be "409"
