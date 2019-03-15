Feature: ContactConfiguration
    As a Centreon admin user
    I want to create a contact
    To configure it

    Background:
        Given I am logged in a Centreon server
        And a contact is configured

    Scenario: Change the properties of a contact
        When I update contact properties
        Then the contact properties are updated

    Scenario: Duplicate one existing contact
        When I duplicate a contact
        Then the new contact has the same properties

    Scenario: Delete one existing contact
        When I delete a contact
        Then the deleted contact is not displayed in the list
