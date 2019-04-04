Feature: Get topology data

  In order to utilize topology params in React
  As a logged in user
  I need to ensure my API returns proper JSON for topology object

  Background:
    Given I am logged in a Centreon server
    And I have a running instance of Centreon API

  Scenario: Healthcheck
    When I make a GET request to "/api/index.php?object=centreon_topology&action=getTopologyByPage&topology_page=1"
    Then the response code should be 200
    And the response has a "topology_id" property
    And the response has a "topology_name" property
    And the response has a "topology_url" property
    And the response has a "is_react" property
