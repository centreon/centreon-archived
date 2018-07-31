Feature: Limit metrics in chart
    As a Centreon Web user
    I want to know if there might be issue with a chart with a lot of metrics
    So that i will not crash my browser

    Background:
        Given I am logged in a Centreon server with configured metrics

    Scenario: Display message and button in performance page
        When I display the chart in performance page
        Then a message says that the chart will not be displayed
        And a button is available to display the chart
