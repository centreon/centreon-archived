Feature: Testing a recurrent Downtime
  As a Centreon user
  I want to be certain that the recurrent downtimes work correctly
  To release quality products

  Background:
    Given I am logged in a Centreon server

  Scenario: Testing a recurrent Downtime on a HostGroup without any ServiceGroup created (Bugfix)
    Given a hostGroup is configured
    And a recurrent downtime on a hostGroup
    When this one gives a downtime
    Then the recurrent downtime started
