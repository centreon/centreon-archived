Feature: Database partitioning
    As a Centreon user
    I want to clean database tables quickly
    To keep it easy to maintain

    Background:
        Given I am logged in a Centreon server

    Scenario: Database partitioning informations
        When I am on database informations page
        Then partitioning informations are displayed

