Feature: Limit metrics in chart
    As a Centreon Web user
    I want to know if there might be issue with a chart with a lot of metrics
    So that i will not crash my browser

    Background:
        Given I am logged in a Centreon server

    Scenario: Display message in chart popin
        Given a service with several metrics
        When i display the chart in the popin
        Then a message says that the chart will not be displayed

    Scenario: Display message and button in perfomance page
        Given a service with several metrics
        When i display the chart in performance page
        Then a message says that the chart will not be displayed and a button is available to display the chart

    Scenario: Display message and button in service details page
        Given a service with several metrics
        When i display the chart in service details page
        Then a message says that the chart will not be displayed and a button is available to display the chart
