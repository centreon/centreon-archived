Feature: ContactGroupCreationDuplication
    As a Centreon admin user
    I want to create a contact group
    To duplicate and delete it

    Background:
        Given I am logged in a Centreon server

    Scenario: Create a contact group
        When I create a contact group
        Then the new record is displayed in the contact groups list

    Scenario: Duplication of a contact group
        Given a contact group is configured
        When I duplicate a contact group
        Then the new contact group is displayed in the contact groups list

    Scenario: Delete a contact group
        Given a contact group is configured
        When I delete a contact group
        Then the deleted contact group is not displayed in the contact groups list
