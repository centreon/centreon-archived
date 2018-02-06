Feature: Edit a connector
    As a Centreon user
    I want to manipulate a connector
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a connector is configured

    Scenario: Change the properties of a connector
        When I change the properties of a connector
        Then the properties are updated

    Scenario: Duplicate one existing connector
        When I duplicate a connector
        Then the new connector has the same properties

    Scenario: Delete one existing connector
        When I delete a connector
        Then the deleted connector is not displayed in the list
