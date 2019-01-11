#features/ModuleApi.feature

Feature: Check health of the CentreonModule's API
  In order to provide data for module page
  As a logged in user
  I need to ensure my API returns proper JSON

  Background:
    Given I am logged in a Centreon server
    And I have a running instance of Centreon API

  Scenario: Healthcheck
    When I make a GET request to "/api/index.php?object=centreon_module&action=list"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property
