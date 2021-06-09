Feature:
  In order to filter properly on resources
  As a user
  I want to manipulate filters using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: User filters
    Given I am logged in

    When I send a GET request to '/api/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 0 elements

    When I send a POST request to '/api/beta/users/filters/events-view' with body:
    """
    {
      "name": "my filter1",
      "criterias": [
        {
          "name": "name1",
          "value": "value1",
          "type": "type1"
        }
      ]
    }
    """
    Then the response code should be "200"

    When I send a GET request to '/api/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 1 elements

    When I send a GET request to '/api/beta/users/filters/events-view/1'
    Then the response code should be "200"
    And the json node "name" should be equal to the string "my filter1"
    And the json node "order" should be equal to the number 1

    When I send a PUT request to '/api/beta/users/filters/events-view/1' with body:
    """
    {
      "name": "filter1",
      "criterias": [
        {
          "name": "name1",
          "value": "value1",
          "type": "type1"
        }
      ]
    }
    """
    Then the response code should be "200"

    When I send a GET request to '/api/beta/users/filters/events-view/1'
    Then the response code should be "200"
    And the json node "name" should be equal to the string "filter1"

    When I send a POST request to '/api/beta/users/filters/events-view' with body:
    """
    {
      "name": "filter2",
      "criterias": [
        {
          "name": "name1",
          "value": "value1",
          "type": "type1"
        }
      ]
    }
    """
    Then the response code should be "200"

    When I send a GET request to '/api/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 2 elements

    When I send a PATCH request to '/api/beta/users/filters/events-view/1' with body:
    """
    {
      "order": 2
    }
    """
    Then the response code should be "200"

    When I send a GET request to '/api/beta/users/filters/events-view/1'
    Then the response code should be "200"
    And the json node "order" should be equal to the number 2

    When I send a GET request to '/api/beta/users/filters/events-view/2'
    Then the response code should be "200"
    And the json node "order" should be equal to the number 1

    When I send a DELETE request to '/api/beta/users/filters/events-view/1'
    Then the response code should be "204"

    When I send a GET request to '/api/beta/users/filters/events-view'
    Then the response code should be "200"
    And the json node "result" should have 1 elements

  Scenario: Updated criterias
    Given I am logged in
    And the following CLAPI import data:
    """
    HG;ADD;hostgroup_test;hostgroup test
    HOST;ADD;host_test;Test host;127.0.0.1;generic-host;central;
    HOST;ADDHOSTGROUP;host_test;hostgroup_test
    """
    And the configuration is generated and exported
    And I wait until host "host_test" is monitored
    And I add a filter linked to hostgroup "hostgroup_test"

    When the following CLAPI import data:
    """
    HG;SETPARAM;hostgroup_test;name;hostgroup_test_2
    """
    And the configuration is generated and exported
    And I wait until hostgroup "hostgroup_test_2" is monitored
    And I update the filter with the creation values
    And I send a GET request to '/api/beta/users/filters/events-view/1'

    Then the response code should be "200"
    And the json node "criterias[0].value[0].name" should be equal to the string "hostgroup_test_2"
