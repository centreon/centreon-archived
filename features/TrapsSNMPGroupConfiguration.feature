Feature: Edit a trap group
    As a Centreon user
    I want to manipulate a trap group
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a trap group is configured

    @critical
    Scenario: Change the properties of a trap group
        When I change the properties of a trap group
        Then the properties are updated

    @critical
    Scenario: Duplicate one existing trap group
        When I duplicate a trap group
        Then the new object has the same properties

    @critical
    Scenario: Delete one existing trap group
        When I delete a trap group
        Then the deleted object is not displayed in the list
