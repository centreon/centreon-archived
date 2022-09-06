Feature:
    In order to maintain centreon platform
    As an administrator
    I want to known the platform installation status

    Background:
        Given a running instance of Centreon Web API
        And the endpoints are described in Centreon Web API documentation

    Scenario: Update platform information
        When I send a GET request to '/api/latest/platform/installation/status'
        Then the response code should be "200"
        And the JSON node "is_installed" should be equal to true

        Given Centreon Web is not installed
        When I send a GET request to '/api/latest/platform/installation/status'
        Then the response code should be "200"
        And the JSON node "is_installed" should be equal to false
