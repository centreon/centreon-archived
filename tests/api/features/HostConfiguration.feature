Feature:
  In order to manipulate hosts
  As a user
  I want to have CRUD api endpoints

  Background:
    Given a running instance of Centreon Web API

  Scenario: Host CRUD
    Given I am logged in
    When I send a GET request to '/beta/monitoring/hosts?search={"host.name":"Centreon-Server"}'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the response should be formatted like JSON format "monitoring/host/listing.json"
    And the json node "result" should have 1 elements
    And the JSON node "result[0].name" should be equal to the string '"Centreon-Server"'

