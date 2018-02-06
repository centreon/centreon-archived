Feature: Centreon backup
    As a Centreon user
    I want to backup my data
    So that I will be safe in case of failure

    Background:
        Given I am logged in a Centreon server

    Scenario: Full backup
        Given the next backup is configured to be full
        When the backup process starts
        Then the whole data is backed up

    Scenario: Partial backup
        Given the next backup is configured to be partial
        When the backup process starts
        Then the modified data is backed up
