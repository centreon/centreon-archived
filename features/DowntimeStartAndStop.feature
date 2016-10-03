Feature: downtime start and stop
  As a Centreon user
  I want to be certain that the downtimes work correctly
  To release quality products

  Background:
    Given I am logged in a Centreon server

#  Scenario: Configure downtime
#    Given a downtime in configuration of a user in other timezone
#    When I save a downtime
#    Then the time of the start and end of the downtime took into account the timezone of the supervised element

  Scenario: Configure recurrent downtime
    Given a recurrent downtime on an other timezone service
    When this one gives a downtime
    Then the time of the start and end of the downtime took into account the timezone of the supervised element
