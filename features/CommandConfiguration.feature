Feature: Edit a command
    As a Centreon user
    I want to manipulate a command
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a command is configured

    Scenario: Change the properties of a command
        When I change the properties of a command
        Then the properties are updated

    Scenario: Duplicate one existing service
        When I duplicate a command
        Then the new command has the same properties

    Scenario: Delete one existing service
        When I delete a command
        Then the deleted command is not displayed in the list

    Scenario: Check if the command appears on the checks page
        When I create a check command
        Then the command is displayed on the checks page

    Scenario: Check if the command appears on the notifications page
        When I create a notification command
        Then the command is displayed on the notifications page

    Scenario: Check if the command appears on the discovery page
        When I create a discovery command
        Then the command is displayed on the discovery page

    Scenario: Check if the command appears on the miscellaneous page
        When I create a miscellaneous command
        Then the command is displayed on the miscellaneous page
