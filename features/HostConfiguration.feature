Feature: HostConfiguration
    As a Centreon admin
    I want to modify an host
    To see if the modification is saved on the Host Page

    Background:
        Given I am logged in a Centreon server
        And an host is configured

    Scenario: Check for template select2
        When I open the new host page
        Then A select box allow to search a unique template and to select it
