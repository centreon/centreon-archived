Feature: Edit a service
    As a Centreon user
    I want to manipulate a host dependency
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a host dependency is configured

    Scenario: Change the properties of a host dependency
        When I change the properties of a host dependency
        Then the properties are updated

    Scenario: Duplicate one existing host dependency
        When I duplicate a host dependency
        Then the new host dependency has the same properties

    Scenario: Delete one existing service
        When I delete a host dependency
        Then the deleted host dependency is not displayed in the list
