Feature:
  In order to monitor hosts by groups
  As a user
  I want to get host group information using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation (version: 3.1)

  Scenario: Host group listing
    Given I am logged in
    When I send a GET request to '/beta/configuration/hosts/groups'
    Then the response should be empty
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"





