Feature: Centreon backup
    As a Centreon user
    I want to backup my data
    To keep safe in case of failure

    Background:
        Given I am logged in a Centreon server

    Scenario: Scheduled backup
        When I check centreon scheduled task
        Then backup is scheduled
