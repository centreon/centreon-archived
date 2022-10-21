Feature: List Contact Groups API
  In order to use List Contact Groups API
  As a logged user
  I need to be able to list Contact Groups

  Background:
    Given a running instance of Centreon Web API

    Scenario: List Contact Groups as an admin user
      Given I am logged in
      And the endpoints are described in Centreon Web API documentation (version: 22.10)

      When I send a GET request to '/api/latest/configuration/contacts/groups'
      Then the response code should be "200"
      And the JSON should be equal to:
      """
        {
          "result": [
            {
              "id": 3,
              "name": "Guest"
            },
            {
              "id": 5,
              "name": "Supervisors"
            }
          ],
          "meta": {
            "page": 1,
            "limit": 10,
            "search": {},
            "sort_by": {},
            "total": 2
          }
        }
      """

    Scenario: List Contact Groups as a non-admin user with rights to Reach API and belonging to a Contact Group
      Given the following CLAPI import data:
      """
        ACLMENU;ADD;kevMenu;kevMenu
        ACLMENU;SETPARAM;kevMenu;activate;1
        ACLMENU;GRANTRW;kevMenu;0;Configuration;Users
        ACLMENU;GRANTRO;kevMenu;0;Configuration;Users;Contact Groups
        CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;0;1;en_US;local
        CONTACT;setparam;kev;reach_api;1
        CG;ADD;kevGroup;kevGroup
        CG;setparam;kevGroup;cg_activate;1
        CG;setparam;kevGroup;cg_type;local
        CG;addcontact;kevGroup;kev;kev
        ACLGROUP;ADD;kevACLGroup;kevACLGroup
        ACLGROUP;SETPARAM;kevACLGroup;activate;1
        ACLGROUP;SETMENU;kevACLGroup;kevMenu
        ACLGROUP;SETCONTACT;kevACLGroup;kev
      """
      And I am logged in with "kev"/"Centreon@2022"

      When I send a GET request to '/api/latest/configuration/contacts/groups'
      Then the response code should be "200"
      And the JSON should be equal to:
      """
        {
          "result": [
            {
              "id": 6,
              "name": "kevGroup"
            }
          ],
          "meta": {
            "page": 1,
            "limit": 10,
            "search": {},
            "sort_by": {},
            "total": 1
          }
        }
      """

    Scenario: List Contact Groups as a non-admin user with rights to Reach API and not belonging to a Contact Group
      Given the following CLAPI import data:
      """
        ACLMENU;ADD;kevMenu;kevMenu
        ACLMENU;SETPARAM;kevMenu;activate;1
        ACLMENU;GRANTRW;kevMenu;0;Configuration;Users
        ACLMENU;GRANTRO;kevMenu;0;Configuration;Users;Contact Groups
        CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;0;1;en_US;local
        CONTACT;setparam;kev;reach_api;1
        ACLGROUP;ADD;kevACLGroup;kevACLGroup
        ACLGROUP;SETPARAM;kevACLGroup;activate;1
        ACLGROUP;SETMENU;kevACLGroup;kevMenu
        ACLGROUP;SETCONTACT;kevACLGroup;kev
      """
      And I am logged in with "kev"/"Centreon@2022"

      When I send a GET request to '/api/latest/configuration/contacts/groups'
      Then the response code should be "200"
      And the JSON should be equal to:
      """
        {
          "result": [],
          "meta": {
            "page": 1,
            "limit": 10,
            "search": {},
            "sort_by": {},
            "total": 0
          }
        }
      """

    Scenario: List Contact Groups as a non-admin user with no rights to Reach API
      Given the following CLAPI import data:
      """
        ACLMENU;ADD;kevMenu;kevMenu
        ACLMENU;SETPARAM;kevMenu;activate;1
        ACLMENU;GRANTRW;kevMenu;0;Configuration;Users
        ACLMENU;GRANTRO;kevMenu;0;Configuration;Users;Contact Groups
        CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;0;1;en_US;local
        CONTACT;setparam;kev;reach_api;0
        CG;ADD;kevGroup;kevGroup
        CG;setparam;kevGroup;cg_activate;1
        CG;setparam;kevGroup;cg_type;local
        CG;addcontact;kevGroup;kev;kev
        ACLGROUP;ADD;kevACLGroup;kevACLGroup
        ACLGROUP;SETPARAM;kevACLGroup;activate;1
        ACLGROUP;SETMENU;kevACLGroup;kevMenu
        ACLGROUP;SETCONTACT;kevACLGroup;kev
      """
      And I am logged in with "kev"/"Centreon@2022"

      When I send a GET request to '/api/latest/configuration/contacts/groups'
      Then the response code should be "403"

    Scenario: List Contact Groups as a non admin user with rights to Reach API, but no rights to access Contact Group menu
      Given the following CLAPI import data:
      """
        CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;0;1;en_US;local
        CONTACT;setparam;kev;reach_api;1
        CG;ADD;kevGroup;kevGroup
        CG;setparam;kevGroup;cg_activate;1
        CG;setparam;kevGroup;cg_type;local
        CG;addcontact;kevGroup;kev;kev
      """
      And I am logged in with "kev"/"Centreon@2022"

      When I send a GET request to '/api/latest/configuration/contacts/groups'
      Then the response code should be "403"
      And the JSON should be equal to:
      """
        {
          "code": 403,
          "message": "You are not allowed to access contact groups"
        }
      """
