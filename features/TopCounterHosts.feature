Feature: Top Counter Host
  As a Centreon User
  I want to use the top counter host actions
  To see the hosts status informations

  Background:
    Given I am logged in a Centreon server

  Scenario: Link to ok hosts
    Given an OK host
    When I click on the chip "count-host-up"
    Then I see the list of hosts filtered by status up

  Scenario: Link to critical hosts
    Given a non-OK host
    When I click on the chip "count-host-down"
    Then I see the list of hosts filtered by status down

  Scenario: Link to unknown hosts
    Given an unreachable host
    When I click on the chip "count-host-unreachable"
    Then I see the list of hosts filtered by status unreachable

  Scenario: Open the summary of hosts status
    When I click on the hosts icon
    Then I see the summary of hosts status
