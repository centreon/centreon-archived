Feature: downtime start and stop
  As a Centreon user
  I want to be certain that the downtimes work correctly
  To release quality products

  Background:
    Given I am logged in a Centreon server
#    And I have a host in London timezone


#  Scenario: Configure downtime
#    Given a downtime in configuration of a user in london timezone
#    When I save a downtime
#    Then the time of the start and end of the downtime took into account the timezone of the supervised element

  Scenario: End of downtime
    Given a fixed downtime on a monitored element
    And the downtime is started
    When the end date of the downtime happens
    Then the downtime is stopped







#  Scenario: Configure downtime
#    Given a downtime in configuration
#    When I save the downtime
#    Then the time of the start and end of the downtime took into account the timezone of the supervised element
