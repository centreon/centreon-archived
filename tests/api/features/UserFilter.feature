Feature:
  In order to monitor a resource
  As a user
  I want to get resources information using api

  Background:
    Given a running instance of Centreon Web API

  Scenario: Resource listing
    Given I am logged in

    When I send a GET request to '/beta/monitoring/hosts?toto=tata'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the response should be formatted like JSON format "monitoring/resource/listing.json"
    And the json node "result" should have 1 elements
    And the JSON node "result[0].name" should be equal to the string "service_ping"
