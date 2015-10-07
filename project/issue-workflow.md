Triaging of issues
------------------

Ticket structuration provides an important way to help user to improve an open source projet. It give the possibilité to contribute easily. 
A ticket can be more quiclky solved by developpers by:  
- describing the issue precisely. This is necessary because it can be difficult for an issue to explain how an end user experiences an problem and what actions they took.
- Giving a contributor accurate information
- checking the the issue is not already existing to avoid issue duplication

If you don't have time to code, consider helping with ticket triage. The community will thank you for saving them time by spending some of yours.

### 1. Ensure the issue contains basic information

Before starting to solved a ticket, please make sure that the issue is enough complete to be treated and fixed. In order to have a minimum of performance in issue treatment, please check that the following elements are available :

- centreon version
- centreon engine version
- centreon broker version
- Operating system used to run Centreon
- Page URL if this is a docs issue

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
| kind/question    | Contains a user or contributor question requiring a response.                                                        |

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
|Status/Solved       |issue is now fixed or implemented. You should find the patch attached to this ticket                                                |
|Status/Duplicate    |issue is in duplicate. you can find in the referenced ticket is the solution or the implementation                                  |
|Status/Wontfix      |issue will not be fixed because it's not in the goal of the project or not compliante with the development strategie of the product.|
|Status/Works for me |issue can not be reproduce. It's works for us...                                                                                    |

If you have any question regarding this workflow, please contact community@centreon.com.

