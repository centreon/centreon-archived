Feature: Edit a meta service dependency
    As a Centreon user
    I want to manipulate a meta service dependency
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a meta service dependency

    Scenario: Change the properties of a meta service dependency
        When I change the properties of a meta service dependency
        Then the properties are updated

    Scenario: Duplicate one existing meta service dependency
        When I duplicate a meta service dependency
        Then the new object has the same properties

    Scenario: Delete one existing meta service dependency
        When I delete a meta service dependency
        Then the deleted object is not displayed in the list
