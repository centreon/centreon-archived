Feature: HostConfiguration
    As a Centreon admin
    I want to modify an host
    To see if the modification is saved on the Host Page

    Background:
        Given I am logged in a Centreon server
        And an host is configured

    Scenario: Edit the name of an host
        When I configure the name of an host
        Then the name has changed on the Host page

    Scenario: Edit the alias of an host
        When I configure the alias of an host
        Then the alias has changed on the Host page

    Scenario: Edit the address of an host
        When I configure the address of an host
        Then the address has changed on the Host page
