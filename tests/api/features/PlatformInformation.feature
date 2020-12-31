Feature:
    In order to update the platform informations
    As a user
    I want to update some of the informations

    Background:
        Given a running instance of Centreon Web API
        And the endpoints are described in Centreon Web API documentation

    Scenario: Update platform informations
        Given I am logged in

        #This step is mandatory, else the platform_topology is empty on the test container.
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

        When I send a PATCH request to '/beta/platform' with body:
        """
        {
            "isRemote": true,
            "apiUsername": "admin",
            "apiCredentials": "centreon",
            "apiScheme": "http",
            "apiPort": 80,
            "centralServerAddress": "10.30.2.137",
            "apiPath": "centreon"
        }
        """
        Then the response code should be "204"