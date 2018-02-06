Feature: Edit a service dependency
    As a Centreon user
    I want to manipulate a service dependency
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a service dependency is configured

    Scenario: Change the properties of a service dependency
        When I change the properties of a service dependency
        Then the properties are updated

    Scenario: Duplicate one existing service dependency
        When I duplicate a service dependency
        Then the new object has the same properties

    Scenario: Delete one existing service dependency
        When I delete a service dependency
        Then the deleted object is not displayed in the list
