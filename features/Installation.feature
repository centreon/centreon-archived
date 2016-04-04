#features/Installation.feature

Feature: Centreon installation
    As a editor Centreon
    I want to display an advertisement at the end of the installation of Centreon
    To inform the user of the existence of the online platform

    Scenario: Installation with internet
        Given a user installing Centreon with internet access
        When I get to the last step of installation
        Then I view the current version of advertising

    Scenario: Installation without internet
        Given a user installing Centreon without internet access
        When I get to the last step of installation
        Then I view the current version of advertising
