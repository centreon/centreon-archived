Feature: Edit a host group dependency
    As a Centreon user
    I want to manipulate a host group dependency
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a host group dependency is configured

    Scenario: Change the properties of a host group dependency
        When I change the properties of a host group dependency
        Then the properties are updated

    Scenario: Duplicate one existing host group dependency
        When I duplicate a host group dependency
        Then the new object has the same properties

    Scenario: Delete one existing host group dependency
        When I delete a host group dependency
        Then the deleted object is not displayed in the list
