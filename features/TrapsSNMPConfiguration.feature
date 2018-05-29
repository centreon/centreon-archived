Feature: TrapsSNMPConfiguration
    As an IT supervisor
    I want to configure SNMP traps
    To monitore a router

    Background:
        Given I am logged in a Centreon server

    Scenario: Creating SNMP trap with advanced matching rule
        When I add a new SNMP trap definition with an advanced matching rule
        Then the trap definition is saved with its properties, especially the content of Regexp field

    Scenario: Modify SNMP trap definition
        When I modify some properties of an existing SNMP trap definition
        Then all changes are saved

    Scenario: Duplicate SNMP trap definition
        When I have duplicated one existing SNMP trap definition
        Then all SNMP trap properties are updated

    Scenario: Delete SNMP trap definition
        When I have deleted one existing SNMP trap definition
        Then this definition disappears from the SNMP trap list
