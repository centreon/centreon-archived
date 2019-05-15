<h1> Pull Request Template </h1>

<h2> Description </h2>

Please include a short resume of the changes and what is the purpose of PR. Any relevant information should be added to help:
* **QA Team** (Quality Assurance) with tests.
* **reviewers** to understand what are the stakes of the pull request.

Fixes # (issue)

<h2> Type of change </h2>

- [ ] Patch fixing an issue (non-breaking change)
- [ ] New functionality (non-breaking change)
- [ ] Breaking change (patch or feature) that might cause side effects breaking part of the Software
- [ ] Updating documentation (missing information, typo...)

<h2> Target serie </h2>

- [ ] 2.8.x
- [ ] 18.10.x
- [ ] 19.04.x
- [ ] 19.10.x (master)

<h2> How this pull request can be tested ? </h2>

Please describe the **procedure** to verify that the goal of the PR is matched. Provide clear instructions so that it can be **correctly tested**.

Any **relevant details** of the configuration to perform the test should be added.

<h2> Checklist </h2>

<h5> Community contributors & Centreon team </h5>

- [ ] I followed the **coding style guidelines** provided by Centreon
- [ ] I have commented my code, especially new **classes**, **functions** or any **legacy code** modified. (***docblock***)
- [ ] I have commented my code, especially **hard-to-understand areas** of the PR.
- [ ] I have made corresponding changes to the **documentation**.
- [ ] I have **rebased** my development branch on the base branch (master, maintenance).

<h5> Centreon team only </h5>

- [ ] I have made sure that the **unit tests** related to the story are successful.
- [ ] I have made sure that **unit tests covers 80%** of the code written for the story.
- [ ] I have made sure that **acceptance tests** related to the story are successful (**local and CI**)
