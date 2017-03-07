Feature: Timezone in monitoring
  As a Centreon user
  I want to views my host timezone
  To get more information

  Background:
    Given I am logged in a Centreon server

#  Scenario: timezone in popin
#    Given a host
#    When I open the popin in the monitoring page
#    Then the timezone of this host is displayed

  Scenario: Timezone in details page
    Given a host
    When I open the host monitoring details page
    Then the timezone of this host is displayed
