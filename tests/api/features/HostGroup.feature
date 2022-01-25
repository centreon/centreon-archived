Feature:
  In order to monitor hosts by groups
  As a user
  I want to get host group information using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Host group listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HG;ADD;Test Host Group;Alias Test host group
    """

    When I send a GET request to '/api/v21.10/configuration/hosts/groups?search={"name": {"$eq": "Test Host Group"}}'
    Then the response code should be "200"
    And the response should be formatted like JSON format "standard/listing.json"
    And the JSON node "result[0].name" should be equal to the string "Test Host Group"
    And the JSON node "result[0].alias" should be equal to the string "Alias Test host group"
