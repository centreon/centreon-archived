Feature: NonAdminContactCreation
    As a Centreon admin user
    I want to create a non admin contact
    New contact is able to log in Centreon Web

    Background:
        Given I am logged in a Centreon server

    Scenario: Create a non admin contact
        When I have filled the contact form
        And clicked on the save button
        Then the new record is displayed in the users list

    Scenario: Check non admin contact can log in Centreon Web
        Given the new non admin user is created
        When I fill login field and Password
        Then the contact is logged to Centreon Web

    Scenario: Duplication of a contact
        Given a contact is configured
        When I duplicate a contact
        Then the new contact is displayed in the user list

    Scenario: Delete a contact
        Given a contact is configured
        When I delete a contact
        Then the deleted contact is not displayed in the user list
