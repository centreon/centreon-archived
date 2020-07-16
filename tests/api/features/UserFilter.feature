Feature:
  In order to filter properly on resources
  As a user
  I want to manipulate filters using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: User filters
    Given I am logged in

    When I send a GET request to '/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 0 elements

    When I send a POST request to '/beta/users/filters/events-view' with body:
    """
    {"name":"my filter1","criterias":[{"filter1":"value1"}]}
    """
    Then the response code should be "200"

    When I send a GET request to '/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 1 elements

    When I send a GET request to '/beta/users/filters/events-view/1'
    Then the response code should be "200"
    And the json node "name" should be equal to the string "my filter1"
    And the json node "order" should be equal to the number 1

    When I send a PUT request to '/beta/users/filters/events-view/1' with body:
    """
    {"name":"filter1","criterias":[{"filter1":"value1"}]}
    """
    Then the response code should be "200"

    When I send a GET request to '/beta/users/filters/events-view/1'
    Then the response code should be "200"
    And the json node "name" should be equal to the string "filter1"

    When I send a POST request to '/beta/users/filters/events-view' with body:
    """
    {"name":"filter2","criterias":[{"filter1":"value1"}]}
    """
    Then the response code should be "200"

    When I send a GET request to '/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 2 elements

    When I send a PATCH request to '/beta/users/filters/events-view/1' with body:
    """
    {"order":2}
    """
    Then the response code should be "200"

    When I send a GET request to '/beta/users/filters/events-view/1'
    Then the response code should be "200"
    And the json node "order" should be equal to the number 2

    When I send a GET request to '/beta/users/filters/events-view/2'
    Then the response code should be "200"
    And the json node "order" should be equal to the number 1

    When I send a DELETE request to '/beta/users/filters/events-view/1'
    Then the response code should be "204"

    When I send a GET request to '/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 1 elements
