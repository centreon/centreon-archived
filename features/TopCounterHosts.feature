Feature: Top Counter Host
  As a Centreon User
  I want to use the top counter host actions
  To see the hosts status informations

  Background:
    Given a Centreon server
    And I am logged in with new feature

  Scenario: Link to ok hosts
    When I click on the chip "ok hosts"
    Then I see the list of hosts filtered by status up

  Scenario: Link to critical hosts
    Given a non-OK host
    When I click on the chip "Down hosts"
    Then I see the list of hosts filtered by status down

  Scenario: Link to unknown hosts
    Given an unreachable host
    When I click on the chip "Unreachable hosts"
    Then I see the list of hosts filtered by status unreachable

  Scenario: Open the summary of hosts status
    When I click on the hosts icon
    Then I see the summary of hosts status

