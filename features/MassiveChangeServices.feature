Feature: Massive Change on services
    As a Centreon administrator
    I want to modify some properties of similar services 
    To configure quickly numerous services at the same time
	
    Background:
        Given I am logged in a Centreon server
        And several services have been created with mandatory properties

    Scenario: Configure by massive change several services with same properties
        When I have applied Massive Change operation to several services
        Then all selected services are updated with the same values
