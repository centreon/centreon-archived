Feature: HostGroupConfiguration
    As a Centreon admin
    I want to modify an host group
    To see if the modification is saved on the host group page

    Background:
        Given I am logged in a Centreon server
        And an host group is configured

    Scenario: Edit the properties of an host group
        When I change the properties of a host group
        Then its properties are updated

    Scenario: Duplicate one existing host group
        When I duplicate a host group
        Then a new host is created with identical properties

    Scenario: Delete one existing host group
        When I delete the host group
        Then the host group is not visible anymore
