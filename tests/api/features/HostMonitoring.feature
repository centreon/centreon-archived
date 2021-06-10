Feature:
  In order to monitor hosts
  As a user
  I want to get host information using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Host listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    HOST;ADD;test2;Test host2;127.0.0.1;generic-host;central;
    """
    And the configuration is generated and exported
    And I wait until host "test2" is monitored

    When I send a GET request to '/api/beta/monitoring/hosts?search={"host.name":{"$rg":"^test2$"}}'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the response should be formatted like JSON format "monitoring/host/listing.json"
    And the json node "result" should have 1 elements
    And the JSON node "result[0].name" should be equal to the string "test2"

    When I send a GET request to '/api/beta/monitoring/hosts'
    Then the json node "result" should have 3 elements

