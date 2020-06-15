Feature:
  In order to manipulate hosts
  As a user
  I want to have CRUD api endpoints

  Background:
    Given a running instance of Centreon API

  Scenario: Host CRUD
    Given I am logged in
    When I send a GET request to "/beta/monitoring/services"
    Then the response should use "listing" centreon JSON format
#    Then the response should be formatted like JSON format "monitoring/service/listing.json"
#    Then the response should be in JSON
    And the JSON node "result[0]" should contain "50"

#    When I send a POST request to "/configuration/hosts" with data provided by "host/host1.json"
#    Then the response code should be "204"

#    When I send a GET request to "/configuration/hosts" with parameters:
#      | key     | value              |
#      | search  | {"name":"host1"}   |
#    Then the response code should be "200"
#    And the json format should be as described in "configuration/hosts/listing.json"
#    And the json node "result" should have 1 elements
#    And the JSON node "result[0].name" should be equal to "host1"
