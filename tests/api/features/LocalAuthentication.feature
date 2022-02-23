Feature:
  In order to get information on the current user
  As a user
  I want retrieve those information

  Background:
    Given a running instance of Centreon Web API
    #And the endpoints are described in Centreon Web API documentation

  Scenario: Get user parameters

    When I log in with "admin" / "bad_password"
    Then the response code should be "401"

    When I log in with "admin" / "bad_password"
    Then the response code should be "401"

    When I log in with "admin" / "bad_password"
    Then the response code should be "401"

    When I log in with "admin" / "bad_password"
    Then the response code should be "401"
    And the JSON should be equal to:
    """
    {
        "code": 401,
        "message": "Authentication failed"
    }
    """

    When I log in with "admin" / "bad_password"
    Then the response code should be "401"
    And the JSON should be equal to:
    """
    {
        "code": 401,
        "message": "User is blocked"
    }
    """

    When I log in with "admin" / "Centreon!2021"
    Then the response code should be "401"
    And the JSON should be equal to:
    """
    {
        "code": 401,
        "message": "User is blocked"
    }
    """
