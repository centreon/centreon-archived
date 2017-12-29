Triaging of issues
------------------

Ticket structuration provides an important way to help user to improve an open source projet. It give the possibility to contribute easily. 
A ticket can be more quickly solved by developpers by:

- Describing the issue precisely. This is necessary because it can be difficult for an issue to explain how an end user experiences an problem and what actions they took.
- Giving a contributor accurate information
- checking the issue is not already existing to avoid duplication

If you don't have time to code, consider helping with ticket triage. The community will thank you for saving them time by spending some of yours.

### 1. Ensure the issue contains basic information

Before starting to solved a ticket, please make sure that the issue is enough complete to be treated and fixed. In order to have a minimum of performance in issue treatment, please check that the following elements are available :

- Centreon Web version (In Centreon Web on login page or on Administration > About)
- Centreon Engine version (In a terminal on your monitoring server /usr/sbin/centengine -V)
- Centreon Broker version (In a terminal on your monitoring server /usr/sbin/cbd -v)
- Operating system used to run Centreon
- PHP version (In a terminal on your monitoring server /usr/bin/php -v)
- MySQL/MariaDB version (In a terminal on your database server /usr/bin/mysql --version)
- Page URL if this is a documentation issue

Depending on the issue, you might not feel all this information is needed. Use your best judgement. If you cannot triage an issue using what its author provided, explain kindly to the author that they must provide the above information to clarify the problem. 

If the author provides the standard information but you are still unable to triage the issue, request additional information. Do this kindly and politely because you are asking for more of the author's time.

If the author does not respond requested information within the timespan of a week, close the issue with a kind note stating that the author can request for the issue to bereopened when the necessary information is provided.

### 2. Classify the Issue

An issue can have multiple of the following labels.

#### Issue kind

| Kind             | Description                                                                                                          |
|------------------|----------------------------------------------------------------------------------------------------------------------|
| kind/bug         | Bugs are bugs.                                                                                                       |
| kind/docs        | Writing or improving documentation                                                                                   |
| kind/enhancement | Enhancement are not bugs or new features but can drastically improve usability or performance of a project component.|
| kind/feature     | Functionality or other elements that the project does not currently support.                                         |
| kind/question    | Contains a user or contributor question requiring a response or missing information from basic.                     |

### 3. Prioritizing issue

When attached to a specific milestone, an issue can be attributed one of the following labels to indicate their degree of priority. 

| Priority    | Description                                                                                                                       |
|-------------|-----------------------------------------------------------------------------------------------------------------------------------|
| priority/P0 | Urgent: Security, critical bugs, blocking issues. P0 basically means drop everything you are doing until this issue is addressed. |
| priority/P1 | Important: P1 issues are a top priority and a must-have for the next release.                                                     |
| priority/P2 | Normal priority: default priority applied.                                                                                        |
| priority/P3 | Best effort: those are nice to have / minor issues.                                                                               |

And that's it. That should be all the information required for a new or existing contributor to come in an resolve an issue.

### 4. Issues status

When an issue is treated or fixed, a status is applied to the issue. There are differents status available on a issue. Please find the following possibilities : 

|Status              |Description                                                                                                                         |
|--------------------|------------------------------------------------------------------------------------------------------------------------------------|
|Status/Solved       |Issue is now fixed or implemented. You should find the patch attached to this ticket                                                |
|Status/Duplicate    |Issue is in duplicate. You can find in the referenced ticket the solution or the implementation                                  |
|Status/Wontfix      |Issue will not be fixed because it's not in the goal of the project or not compliante with the development strategie of the product.|
|Status/Works for me |Issue can not be reproduce. It works for us...                                                                                    |
|Status/Wong project |Issue is related to another Centreon Project (ex: Centreon Engine, Centreon Broker...)                                              |
|Status/Validated    |Issue is validated by QA Team and is ready for Release Candidate                                                                    |

When an issue is closed, it's fixed but it validate by QA Team when the label Status/Validated is applied.

If you have any question regarding this workflow, please contact community@centreon.com.

