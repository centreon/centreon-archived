Feature: ServiceGroupConfiguration
    As a Centreon admin
    I want to manipulate an host
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a service group is configured

    Scenario: Change the properties of a service group
        When I change the properties of a service group
        Then the properties are updated

    Scenario: Duplicate one existing service group
        When I duplicate a service group
        Then the new service group has the same properties

    Scenario: Delete one existing service group
        When I delete a service group
        Then the deleted service group is not displayed in the service group list
