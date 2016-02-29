Feature: Add Shift in Select2 
    As a Centreon user
    I want to use Shift-key in Select2
    So that I can select several items

    Background:
        Given I am logged in

    Scenario: Select several items with shift key
        Given a selected object
        And a selected select2 
        When I hold shift key
        And click on a first item
        And click on an another item
        Then the items between the two items are selected
