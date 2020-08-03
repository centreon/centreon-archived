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
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    """
    And the configuration is generated and exported
    And I wait until host "test" is monitored
    And I send a GET request to '/beta/monitoring/hosts?search={"host.name":"test"}'
    And I store response values in:
      | name   | path         |
      | hostId | result[0].id |
    And I wait to get 1 result from '/beta/monitoring/hosts/<hostId>/timeline'

    When I send a GET request to '/beta/monitoring/hosts/<hostId>/timeline'

    Then the JSON node "result[0].content" should contain "INITIAL HOST STATE"

  Scenario: Service timeline
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;test;test_service1;Ping-LAN;
    """
    And the configuration is generated and exported
    And I wait until service "test_service1" from host "test" is monitored
    And I send a GET request to '/beta/monitoring/services?search={"$and":[{"host.name":"test"},{"service.description":"test_service1"}]}'
    And I store response values in:
      | name      | path              |
      | hostId    | result[0].host.id |
      | serviceId | result[0].id      |
    And I wait to get 1 result from '/beta/monitoring/hosts/<hostId>/services/<serviceId>/timeline'

    When I send a GET request to '/beta/monitoring/hosts/<hostId>/services/<serviceId>/timeline'

    Then the JSON node "result[0].content" should contain "INITIAL SERVICE STATE"
