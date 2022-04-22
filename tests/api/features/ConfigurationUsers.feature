Feature:
  In order to get information on the current user
  As a user
  I want retrieve those information

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Get user parameters
    Given I am logged in
    And the following CLAPI import data:
    """
    CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;1;1;en_US;local
    """

    When I send a GET request to '/api/v22.04/configuration/users?search={"alias":"kev"}'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
    {
        "result": [
            {
                "id": 20,
                "alias": "kev",
                "name": "kev",
                "email": "kev@localhost",
                "is_admin": true
            }
        ],
        "meta": {
            "page": 1,
            "limit": 10,
            "search": {
                "$and": {
                    "alias": "kev"
                }
            },
            "sort_by": {},
            "total": 1
        }
    }
    """

