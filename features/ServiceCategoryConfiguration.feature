Feature: Edit a service category
    As a Centreon user
    I want to manipulate a service
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a service category is configured

    Scenario: Change the properties of a service category
        When I change the properties of a service category
        Then the properties are updated

    Scenario: Duplicate one existing service category
        When I duplicate a service category
        Then the new service category has the same properties

    Scenario: Delete one existing service
        When I delete a service category
        Then the deleted service category is not displayed in the list
