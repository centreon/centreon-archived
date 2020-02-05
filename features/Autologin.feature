Feature: Autologin
    As a Centreon Web user
    I want to use the Autologin feature
    So that I can access centreon without using the login page

    Background:
        Given I am logged in a Centreon server

    Scenario: Connection via autologin
        Given the user with autologin enabled
        When the user generates autologin key
        Then the user arrives on the configured page for its account

    Scenario: Connection via autologin with topology
        Given the user with autologin enabled
        When the user generates autologin key
        Then the user enters a topology and arrives at the linked page
