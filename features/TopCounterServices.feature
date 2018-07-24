Feature: Top Counter Service
  As a Centreon User
  I want to use the top counter service actions
  To see the services status informations

  Background:
    Given a Centreon server
    And I am logged in with new feature
    And I have a passive service

  Scenario: Link to ok services
    When I click on the chip "Ok services"
    Then I see the list of services filtered by status ok

  Scenario: Link to critical services
    Given a critical service
    When I click on the chip "Critical services"
    Then I see the list of services filtered by status critical

  Scenario: Link to warning services
    Given a warning service
    When I click on the chip "Warning services"
    Then I see the list of services filtered by status warning

  Scenario: Link to unknown services
    Given a unknown service
    When I click on the chip "Unknown services"
    Then I see the list of services filtered by status unknown

  Scenario: Open the summary of services status
    When I click on the services icon
    Then I see the summary of services status

