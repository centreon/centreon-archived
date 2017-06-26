Feature: ContactConfiguration
    As a Centreon admin user
    I want to create a contact
    To configure it

    Background:
        Given I am logged in a Centreon server
        Given a contact is configured

    Scenario: Edit a contact
        When I update contact properties
        Then the contact properties are updated
