Feature: Authentication to Api V1
    Depending of User and Access Rights
    I need to ensure that the user is allowed or not to authenticate to API

    Background:
        Given a Centreon server

    Scenario: Authenticate as admin
        When I make an authentication request with credentials "admin"/"Centreon!2021"
        Then the response code should be 200
        And the response has a "authToken" property

    Scenario: Authenticate as non-admin user with no right to reach front end and right to reach api
        Given the following CLAPI import data:
        """
        CONTACT;ADD;test;test;test@localhost;Centreon!2021;0;0;en_US;local
        CONTACT;setparam;test;reach_api;1
        """
        When I make an authentication request with credentials "test"/"Centreon!2021"
        Then the response code should be 200
        And the response has a "authToken" property

    Scenario: Authenticate as non-admin user with no right to reach api
        Given the following CLAPI import data:
        """
        CONTACT;ADD;test;test;test@localhost;Centreon!2021;0;1;en_US;local
        """
        When I make an authentication request with credentials "test"/"Centreon!2021"
        Then the response code should be 403

    Scenario: Authenticate as non-admin user with right to reach configuration api and no right to reach realtime api
        Given the following CLAPI import data:
        """
        CONTACT;ADD;test;test;test@localhost;Centreon!2021;0;1;en_US;local
        CONTACT;setparam;test;reach_api;1
        """
        When I make an authentication request with credentials "test"/"Centreon!2021"
        Then the response code should be 200

    Scenario: Authenticate as non-admin user with right to reach realtime api and no right to reach configuration api
        Given the following CLAPI import data:
        """
        CONTACT;ADD;test;test;test@localhost;Centreon!2021;0;0;en_US;local
        CONTACT;setparam;test;reach_api_rt;1
        """
        When I make an authentication request with credentials "test"/"Centreon!2021"
        Then the response code should be 200

    Scenario: Authenticate with invalid credentials
        When I make an authentication request with credentials "admin"/"invalidPassword"
        Then the response code should be 401