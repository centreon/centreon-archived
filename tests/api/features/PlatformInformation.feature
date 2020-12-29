Feature:
    In order to update the platform informations
    As a user
    I want to update some of the informations

    Background:
        Given a running instance of Centreon Web API
        And the endpoints are described in Centreon Web API documentation

    Scenario: Update platform informations
        Given I am logged in
        When I send a PATCH request to '/beta/platform' with body:
        """
        {
            "isRemote": true,
            "apiUsername": "admin",
            "apiCredentials": "centreon",
            "centralServerAddress": "192.168.0.1",
            "proxy": {
                "proxyHost": "myproxy.com",
                "proxyPort": 3128,
                "proxyUser": "admin",
                "proxyPassword": "centreon"
            }
        }
        """
        Then the response code should be "204"