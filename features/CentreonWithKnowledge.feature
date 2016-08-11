Feature: Centreon with knowledge
    As a Centreon user
    I want to link my hosts and services supervised to wiki s procedures
    To have quickly additional information on my hosts and services

    Background:
        Given I am logged in a Centreon server with MediaWiki installed

    Scenario: Check Host Knowledge
        Given a host configured
        When I add a procedure concerning this host in MediaWiki
        Then a link towards this host procedure is available in configuration

    Scenario: Check Service Knowledge
        Given a service configured
        When I add a procedure concerning this service in MediaWiki
        Then a link towards this service procedure is available in configuration






