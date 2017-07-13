Feature: ContactGroupConfiguration
    As a Centreon admin
    I want to modify an host
    To see if the modification is saved on the contact group page

    Background:
        Given I am logged in a Centreon server
        And a contact group is configured

    Scenario: Edit a contact group
        When I update the contact group properties
        Then the contact group properties are updated
