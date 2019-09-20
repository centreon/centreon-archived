Feature: Check XSS vulnerability on poller resource
    As a Centreon user
    I want to add a new poller resource
    To check XSS vulnerability on the poller resource list page

    Background:
        Given I am logged in a Centreon server

    @critical
    Scenario: Check XSS vulnerability on the pollers resources list page
        When I add a poller resource
        Then The html is not interpreted on the pollers resources list page