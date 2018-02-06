Feature: Freeze fields on blocked objects
    As a Centreon admin
    I want to freeze all the fields of a blocked object template
    To let the user know that the content is in read-only

    Background:
        Given I am logged in a Centreon server

    Scenario:
        Given a blocked object template
        When i open the form
        Then the fields are frozen