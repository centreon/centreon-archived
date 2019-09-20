Feature: Virtual Metric Handle
    As a Centreon user
    I want to use virtual metric
    To calculate specific values I need to check

    Background:
        Given I am logged in a Centreon server with configured metrics

    Scenario: Create a virtual metric
        When I add a virtual metric
        Then all properties are saved

    Scenario: Duplicate a virtual metric
        Given an existing virtual metric
        When I duplicate a virtual metric
        Then all properties are copied except the name

    Scenario: Delete a virtual metric
        Given an existing virtual metric
        When I delete a virtual metric
        Then the virtual metric disappears from the Virtual metrics list

    @listFilter
    Scenario: Filter a virtual metric in the list page
        Given an existing virtual metric
        When I filter the list to find default entity
        Then in the list must be default entity only

    @security @listFilter
    Scenario Outline: Try to inject SQL by the filter on the list page
        Given an existing virtual metric
        When I filter the list with <text>
        Then the list must be empty

        Examples:
          |    text    |
          | "' AND (SELECT 7076 FROM(SELECT COUNT(*),CONCAT(0x71707a6a71,(SELECT(ELT(7076=7076,1))),0x7162787071,FLOOR(RAND(0)*2))x FROM INFORMATION_SCHEMA.PLUGINS GROUP BY x)a)-- edsY" |
          | "' AND (SELECT * FROM (SELECT(SLEEP(5)))IBjx)-- gFbi" |
