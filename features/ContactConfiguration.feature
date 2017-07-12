Feature: ContactConfiguration
    As a Centreon admin user
    I want to create a contact
    To configure it

    Background:
        Given I am logged in a Centreon server

    Scenario: Edit a contact
        Given a contact is configured
        When I update contact properties
        Then the contact properties are updated
