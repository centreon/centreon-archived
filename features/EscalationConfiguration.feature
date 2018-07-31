Feature: Edit an escalation
    As a Centreon user
    I want to manipulate an escalation
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And an escalation is configured

    Scenario: Change the properties of an escalation
        When I change the properties of an escalation
        Then the properties are updated

    Scenario: Duplicate one existing escalation
        When I duplicate an escalation
        Then the new escalation has the same properties

    Scenario: Delete one existing service
        When I delete an escalation
        Then the deleted escalation is not displayed in the list
