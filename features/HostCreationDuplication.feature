Feature: HostCreationDuplication
    As a Centreon admin user
    I want to create an host
    To duplicate an delete it

    Background:
        Given I am logged in a Centreon server

    Scenario: Create a host
        When I create a host
        Then the new record is displayed in the hosts list

    Scenario: Duplication of a host
        Given a host is configured
        When I duplicate a host
        Then the new host is displayed in the hosts list

    Scenario: Delete a host
        Given a host is configured
        When I delete a host
        Then the deleted host is not displayed in the hosts list
