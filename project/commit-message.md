The good way to write a commit message
--------------------------------------

The goal of this documentation is to normalize git commit message and give to the community a better visibily.

1. The first line should always be 50 characters or less and it should be followed by a blank line. The line is a summary of the fix or the feature.

2. After the blank line, you can add a full description of the fix or the feature. This description can be multiline and include a list. The list is define by lines beginning by a space and a wildcard. This lines should always be 72 characters or less.

3. The last part is some extented external information.

  - Fix: #1111 -- When you fix a Github issue
  - Resolve: #1111 -- When you fix a Github issue
  - Ref: #1111, #1112 -- When your commit reference to a Github issue
  - See also: #1111, #1112 -- When your commit reference to a Github issue
  - Agile: #CE2-1111 -- Related to a Jira Agile story
  - Link: a2d355ed -- Link the commit to another commit for better following

4. The format of issue for Github can be :

  - Fix: #1111
  - Fix: user/project#1111 -- For link the commit to a issue of another project
  
  
The limit of characters by line is to match on Github display and terminal display. 

----

### Example of a good commit message

Fix a problem to save an user in configuration

The issue to save an user come form a bad variable setting in file 
www/configuration/user.php.

A better control of username format has be incluced too.

Fix: #1111
Agile: #CE2-111
