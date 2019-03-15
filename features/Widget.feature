#features/Widget.feature

Feature: Widget
    As a company administrator
    I want to manage my widgets
    So that I can use widgets I want

    Background:
        Given I am logged in a Centreon server

    Scenario: Widget installation
        Given a widget is ready to install
        When I install the widget
        Then the widget is installed

    Scenario: Widget remove
        Given a widget is ready to remove
        When I remove the widget
        Then the widget is removed
