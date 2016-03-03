# Bug report / Feature request #

If you wish to open a bug report or ask for a new feature, you are
reading the proper page ! We value your feedback very much. However to
keep things simple for you and for us, some basic rules must be set.

## Issue workflow ##

Here is the basic issue workflow for most Centreon software. Steps and
transitions are explained below.

![Centreon issue workflow](project/issue-workflow.png)

Step | Description
-----|------------
Open | This is the default state when a new issue is open. It should be reviewed by a team member soon.
Need more information | The issue does not contain enough information to make something out of it.
In backlog | For a bug, it was confirmed. For a feature request it was granted. However it does not have enough priority to be scheduled for release yet.
Scheduled for release | Issue is scheduled for development and release. It will likely be implemented within the next month or so.
Implemented | Issue was developed. Code is available from the repository.
Closed | Either the issue was rejected or released.

Transition | Steps | Description
-----------|-------|------------
Question | Open -> Need more information | Issue does not contain enough information. The team member will ask for clarification.
Dont't want to implement / Duplicate | Open -> Closed | Some reason made us close this issue directly. The most common cases are duplication of an existing issue, we do not want to implement it or we cannot reproduce it.
Backlog refinement | Open -> In backlog | Features or bugs are added to the backlog if they are desirable.
Emergency | Open -> Scheduled for release | If an issue has very high priority (security hole, software crash, [Centreon support customer incident](https://www.centreon.com/en/technical-support-expertise/support/), ...) it is directly scheduled for development and release.
Reply | Need more information -> Open | The issue creator gave more information on the issue. It will be reviewed again.
No answer | Need more information -> Closed | Too much time elapsed since the team member asked for clarification. Issue will be closed to prevent it from polluting the list.
Sprint planning | In backlog -> Scheduled for release | The issue was added to the current sprint. It is likely to be implemented within the next month or so.
Story done | Scheduled for release -> Implemented | Issue was completed. Code, tests and documentation were committed to the repository.
Release | Implemented -> Closed | Issue is attached to a specific milestone that was released. It is available from binary and source packages.
