Feature:
  In order to monitor hosts by groups
  As a user
  I want to get host group information using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation (version: 2.1)

  Scenario: Host group listing
    Given I am logged in
    And the following CLAPI import data:
        """
        HG;ADD;Linux-Servers;All linux servers
        """
    And the configuration is generated and exported
    When I send a GET request to '/beta/configuration/hosts/groups'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the JSON node "result[0].name" should be equal to the string "Linux-Servers"



