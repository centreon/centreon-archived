Feature: Submit A Result To A Passive Service
    As a Centreon user
    I want to force the status and output of a passive service
    To launch a specific event
	
    Background:
        Given I am logged in a Centreon server

    Scenario: Submit result to a passive service
        Given one passive service has been configured using arguments status and output exists                     
        When I submit some result to this service
        Then the values are set as wanted in Monitoring > Status details page


