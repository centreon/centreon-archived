Feature:
    In order to update the platform information
    As a user
    I want to update some of the information

    Background:
        Given a running instance of Centreon Web API
        And the endpoints are described in Centreon Web API documentation

    Scenario: Update platform information
        Given I am logged in

        When I send a PATCH request to '/latest/platform' with body:
        """
        {
            "apiUsername": "admin",
            "apiCredentials": "centreon",
            "apiScheme": "http",
            "apiPort": 80,
            "apiPath": "centreon"
        }
        """
        Then the response code should be "204"