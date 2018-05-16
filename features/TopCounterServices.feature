Feature: Top Counter Service
    As a Centreon User
    I want to use the top counter service actions
    To see the services status informations

    Background:
        Given a Centreon server
        And I am logged in with new feature

    Scenario: Link to critical services
      When I click on the chip "Critical services"
      Then I see the list of services filtered by status critical

    Scenario: Link to warning services
      When I click on the chip "Warning services"
      Then I see the list of services filtered by status warning

    Scenario: Link to unknown services
      When I click on the chip "Unknown services"
      Then I see the list of services filtered by status unknown

    Scenario: Open the summary of services status
      When I click on the services icon
      Then I see the summary of services status

