Feature: Edit a service group dependency
    As a Centreon user
    I want to manipulate a service group dependency
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a service group dependency

    Scenario: Change the properties of a service group dependency
        When I change the properties of a service group dependency
        Then the properties are updated

    Scenario: Duplicate one existing service group dependency
        When I duplicate a service group dependency
        Then the new object has the same properties

    Scenario: Delete one existing service group dependency
        When I delete a service group dependency
        Then the deleted object is not displayed in the list
