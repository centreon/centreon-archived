Feature:
  In order to know what happened on resources
  As a user
  I want to get monitoring timeline of a resource

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Host timeline
    Given I am logged in
    And the following CLAPI import data:
    """
    CMD;ADD;dummy_down;check;exit 2
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    HOST;SETPARAM;test;check_command;dummy_down
    """
    And the configuration is generated and exported
    And I wait until host "test" is monitored
    And I send a GET request to '/api/beta/monitoring/hosts?search={"host.name":"test"}'
    And I store response values in:
      | name   | path         |
      | hostId | result[0].id |
    And I send a POST request to '/api/beta/monitoring/hosts/<hostId>/check' with body:
    """
    {}
    """
    And I wait to get 1 result from "/api/beta/monitoring/hosts/<hostId>/timeline" (tries: 30)

    When I send a GET request to '/api/beta/monitoring/hosts/<hostId>/timeline?search={"type":"event"}'

    Then the JSON node "result[0].status.name" should be equal to the string "DOWN"

  Scenario: Service timeline
    Given I am logged in
    And the following CLAPI import data:
    """
    CMD;ADD;dummy_code_2;check;exit 2
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;test;test_service1;generic-service;
    SERVICE;SETPARAM;test;test_service1;check_command;dummy_code_2
    """
    And the configuration is generated and exported
    And I wait until service "test_service1" from host "test" is monitored
    And I send a GET request to '/api/beta/monitoring/services?search={"$and":[{"host.name":"test"},{"service.description":"test_service1"}]}'
    And I store response values in:
      | name      | path              |
      | hostId    | result[0].host.id |
      | serviceId | result[0].id      |
    And I send a POST request to '/api/beta/monitoring/hosts/<hostId>/services/<serviceId>/check' with body:
    """
    {}
    """
    And I wait to get 1 result from "/api/beta/monitoring/hosts/<hostId>/services/<serviceId>/timeline" (tries: 30)

    When I send a GET request to '/api/beta/monitoring/hosts/<hostId>/services/<serviceId>/timeline?search={"type":"event"}'

    Then the JSON node "result[0].status.name" should be equal to the string "UNKNOWN"
