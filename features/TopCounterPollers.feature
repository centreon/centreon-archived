Feature: Top Counter Host
    As a Centreon User
    I want to use the top counter host actions
    To see the pollers status informations

    Background:
        Given a Centreon server
        And I am logged in with new feature

    Scenario: Link to pollers configuration
      When I click on the pollers icon and I click on the configuration button
      Then I see the list of pollers configuration
