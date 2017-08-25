Feature: Testing A Configuration Proxy
    As a Centreon user
    I want to test my proxy configuration
    So that to verify it
    
    Background:
        Given I am logged in a Centreon server with a configured proxy

    Scenario: Proxy settings with a correct connexion
        Given a Centreon user on the Centreon UI page with a proxy url and port correctly configured
        When I click on the test configuration button
        Then a popin displays a successful connexion 

    Scenario: Proxy settings with a wrong connexion
        Given a Centreon user on the Centreon UI page with a proxy url and port wrongly configured
        When I click on the test configuration button 
        Then a popin displays an error message
