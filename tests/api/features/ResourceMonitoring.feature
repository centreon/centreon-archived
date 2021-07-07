Feature:
  In order to monitor a resource
  As a user
  I want to get resources information using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Resource listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;host_test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;host_test;service_ping;Ping-LAN
    """
    And the configuration is generated and exported
    And I wait until host "host_test" is monitored
    And I wait until service "service_ping" from host "host_test" is monitored

    When I send a GET request to '/beta/monitoring/resources?search={"s.description":{"$rg":"^service_ping$"}}'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the json node "result" should have 1 elements
    And the JSON node "result[0].name" should be equal to the string "service_ping"