Feature: HostConfiguration
    As a Centreon admin
    I want to modify an host
    To see if the modification is saved on the Host Page

    Background:
        Given I am logged in a Centreon server
        And an host is configured

    Scenario: Edit the name of an host
        When I change the properties of a host
        Then its properties are updated

    Scenario: Duplicate one existing host
        When I duplicate a host
        Then a new host is created with identical properties

    Scenario: Delete one existing host
        When I delete the host
        Then the host is not visible anymore
