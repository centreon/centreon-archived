Feature: Set service downtime
  As a Centreon user
  I want to put downtimes and comments on my meta-services
  To manage them

  Background:
    Given I am logged in a Centreon server
    And I have a meta service

  Scenario: Place a comment
    When I place a comment
    Then this one appears in the interface

  Scenario: Set a downtime
    When I place a downtime
    Then this one appears in the interface in downtime

  Scenario: Cancel a downtime
    Given I place a downtime
    When I cancel a downtime
    Then this one does not appear in the interface
