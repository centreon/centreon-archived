Feature: Massive Change on Hosts
    As a Centreon administrator
    I want to modify some properties of similar hosts
    To configure quickly numerous hosts at the same time
	
    Background:
        Given I am logged in a Centreon server
        And several hosts have been created with mandatory properties

    Scenario: Configure by massive change several hosts with same properties
        When I have applied Massive Change operation to several hosts
        Then all selected hosts are updated with the same values
