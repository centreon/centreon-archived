Feature: URI
    As a Centreon user
    I want to ad URIs in plugin output or in comments
    To access the link from Centreon

    Background:
        Given I am logged in a Centreon server

    Scenario: URI in service output
        Given a plugin output which contains an URI
        When I click on the link in the service output
        Then a new tab is open to the link

    Scenario: URI in comments
        Given a comment which contains an URI
        When I click on the link in the comment
        Then a new tab is open to the link
