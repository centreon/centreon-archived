Feature:
    In order to access the API
    As a visitor
    I want to login to the API

    Background:
        Given a running instance of Centreon Web API

    Scenario:
        Given I log in with invalid credentials
        And the response code should be "401"
        And the JSON node "code" should be equal to the number "401"
        And the JSON node "error" should be equal to the string "Invalid credentials"