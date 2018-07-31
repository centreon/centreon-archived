Feature: Command arguments
    As a Centreon user
    I want to see and configure the arguments of a command
    So that i can customize my command according to my hosts or services
    
    Background:
        Given I am logged in a Centreon server
        
    #Scenario: Display Service command arguments
        #Given a service being configured
        #When i select a check command
        #Then Arguments of this command are displayed for the service
        #And i can configure those arguments
        
    Scenario: Display Host command arguments
        Given a host being configured
        When i select a check command
        Then Arguments of this command are displayed for the host
        And i can configure those arguments
