Feature: Edit a meta service
    As a Centreon user
    I want to manipulate a meta service
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a meta service is configured

    Scenario: Change the properties of a meta service
        When I change the properties of a meta service
        Then the properties are updated

    Scenario: Duplicate one existing meta service
        When I duplicate a meta service
        Then the new meta service has the same properties

    Scenario: Delete one existing meta service
        When I delete a meta service
        Then the deleted meta service is not displayed in the list
