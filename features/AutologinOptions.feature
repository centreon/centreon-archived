Feature: Autologin Options
    As a Centreon user
    I want to display specific Centreon web Pages on a large screen device
    So that supervisors can watch the monitoring easily in the office room

    Background:
        Given I am logged in a Centreon server
        And one autologin key has been generated
        And the autologin option is enabled

    Scenario: Autologin with full screen option
        When I type the autologin url with the fullscreen option in my web browser
        Then Centreon default page is displayed without the menus and the header

    Scenario: Autologin to Reporting Dashboards Hosts page
        When I type the autologin url with the option page 30701
        Then Reporting > Dashboards > Hosts page is displayed
