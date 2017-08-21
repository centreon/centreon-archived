Feature: Edit a host group service
    As a Centreon user
    I want to manipulate a service
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a service is configured

    Scenario: Change the properties of a host group service
        When I change the properties of a host group service
        Then the properties are updated

    Scenario: Duplicate one existing host group service
        When I duplicate a host group service
        Then the new host group service has the same properties

    Scenario: Delete one existing host group service
        When I delete a host group service
        Then the deleted host group service is not displayed in the list
