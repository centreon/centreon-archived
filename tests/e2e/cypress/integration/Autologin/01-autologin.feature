Feature: Autologin
    
    Scenario: Enable autologin on the platfrom
        Given An administrator is logged in the plateform 
        When The administrator activates autologin on the plateform
        Then Autologin is activatd on the plateform 

	Scenario: Generate autologin key
		Given I am on user page
		When I generate autologin key
		Then The autologin key is properly generated

    Scenario: Generate autologin link
        When I generate an autologin link  
        Then I can use it in the browser
