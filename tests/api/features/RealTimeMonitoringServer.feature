Feature:
  In order to monitor a server
  As a user
  I want to get real time monitoring server information using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Real Time Monitoring Server Listing
    Given I am logged in
    And the configuration is generated and exported
    And I wait to get 1 result from "/beta/monitoring/servers" (tries: 30)
    When I send a GET request to '/beta/monitoring/servers'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the JSON node "result[0].name" should be equal to the string "Central"