Feature: NonAdminContactCreation
    As a Centreon admin user
    I want to create a non admin contact
    New contact is able to log in Centreon Web

    Background:
        Given I am logged in a Centreon server

    Scenario: Basic operations on contacts
        When I create a contact
        And I duplicate it
        And I delete it
        Then the duplicated contact is displayed in the user list
        And I can logg in Centreon Web with the duplicated contact
        And the deleted contact is not displayed in the user list
