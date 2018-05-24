Feature: Testing A Configuration Proxy
    As a Centreon user
    I want to test my proxy configuration
    So that to verify it

    Scenario: Proxy settings with a correct connection
        Given I am logged in a Centreon server with a configured proxy
        When I test the proxy configuration in the interface
        Then a popin displays a successful connexion

    Scenario: Proxy settings with a wrong connection
        Given I am logged in a Centreon server with a wrongly configured proxy
        When I test the proxy configuration in the interface
        Then a popin displays an error message
