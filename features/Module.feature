#features/Module.feature

Feature: Module
    As a company administrator
    I want to manage my modules
    So that I can use modules I want

    Background:
        Given I am logged in a Centreon server

    Scenario: Module installation
        Given a module is ready to install
        When I install the module
        Then the module is installed
