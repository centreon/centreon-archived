Feature:
    In order to access the API
    As a visitor
    I want to login to the API

    Background:
        Given a running instance of Centreon Web API

    Scenario:
        Given the endpoints are described in Centreon Web API documentation
        When I log in with invalid credentials
        Then the response code should be "500"
        And the JSON node "code" should be equal to the number "500"
        And the JSON node "message" should be equal to the string "Authentication failed"

    # Internal local authentication
    Scenario:
        When I am logged in with local provider
        Then the response code should be "200"
        And the header "set-cookie" should contain "PHPSESSID="
