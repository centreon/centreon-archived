#features/TimeperiodsApi.feature

Feature: Check health of the Timeperiods API
  In order to manage time periods
  I need to ensure my API returns proper JSON

  Background:
    Given I am logged in a Centreon server
    And I have a running instance of Centreon API

  Scenario: Healthcheck of list
    When I make a GET request to "/api/index.php?object=centreon_timeperiod&action=list"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property
    And the property "result" has value
    """
    {"pagination":{"total":4,"offset":0,"limit":4},"entities":[{"id":"1","name":"24x7","alias":"Always"},{"id":"2","name":"none","alias":"Never"},{"id":"3","name":"nonworkhours","alias":"Non-Work Hours"},{"id":"4","name":"workhours","alias":"Work hours"}]}
    """