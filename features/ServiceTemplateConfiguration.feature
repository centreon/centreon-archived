Feature: Edit a service template
    As a Centreon user
    I want to manipulate a service
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a service template is configured

    Scenario: Change the properties of a service template
        When I change the properties of a service template
        Then the properties are updated

    Scenario: Duplicate one existing service template
        When I duplicate a service template
        Then the new service template has the same properties

    Scenario: Delete one existing service template
        When I delete a service template
        Then the deleted service template is not displayed in the list
