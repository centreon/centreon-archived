Feature: Handle Centreon Modules / Widgets

  In order to manage my modules and widgets
  As a logged in user
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given I am logged in a Centreon server
    And I have a running instance of Centreon API

  Scenario: List Modules
    When I make a GET request to "/api/index.php?object=centreon_module&action=list"
    Then the response code should be 200
    And the response has a "status" property
    And the response has a "result" property

  Scenario: Install and remove module
    Given I have a non-installed module ready for installation
    When I make a POST request to "/api/index.php?object=centreon_module&action=install&id=centreon-test&type=module"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property

    When I make a GET request to "/api/index.php?object=centreon_module&action=details&id=centreon-test&type=module"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property

    When I make a DELETE request to "/api/index.php?object=centreon_module&action=remove&id=centreon-test&type=module"
    Then the response code should be 200
    And the response has a "status" property
