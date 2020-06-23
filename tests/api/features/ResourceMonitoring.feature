Feature:
  In order to monitor a resource
  As a user
  I want to get resources information using api

  Background:
    Given a running instance of Centreon Web API

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

    When I send a GET request to '/beta/monitoring/resources?search={"service.description":{"$rg":"ping$"}}'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the response should be formatted like JSON format "monitoring/service/listing.json"
    # ping (from default container data) and service_ping should be returned
    And the json node "result" should have 2 elements
    And the JSON node "result[0].name" should be equal to the string "service_ping"

    When I send a GET request to '/beta/monitoring/services'
    Then the json node "result" should have 1 elements