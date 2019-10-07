Feature: Check health of the Monitoring - Service API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And I have a running instance of Centreon API
    And Get authentication token for username 'admin' and password 'centreon'
