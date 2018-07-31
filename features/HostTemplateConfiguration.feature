Feature: Edit host template
    As a Centreon user
    I want to access to host template configuration easily
    To edit configuration quickly

    Background:
       Given I am logged in a Centreon server

    Scenario: Edit parent of an host
        Given an host inheriting from an host template
        When I configure the host
        Then I can configure directly its parent template

    Scenario: Edit parent of an host template
        Given an host template inheriting from an host template
        When I configure the host template
        Then I can configure directly its parent template
