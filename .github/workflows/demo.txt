workflow "Branch notification" {
  on = "push"
  resolves = ["Add Comment"]
}

action "Jira Login" {
  uses = "atlassian/gajira-login@v1.0.0"
  secrets = ["JIRA_API_TOKEN", "JIRA_USER_EMAIL", "JIRA_BASE_URL"]
}

action "Detect Issue in branch" {
  uses = "atlassian/gajira-find-issue-key@v1.0.0"
  needs = ["Jira Login"]
  args = "--from=branch"
}

action "Add Comment" {
  uses = "atlassian/gajira-comment@v1.0.0"
  needs = ["Detect Issue in branch"]
  args = "\"{{event.pusher.name}} [pushed|{{event.compare}}] {{event.commits.length}} commits to {{event.ref}} in {{ event.repository.full_name}}\""
}

workflow "Master transition" {
  on = "push"
  resolves = ["Transition to done"]
}

action "Filters for GitHub Actions" {
  uses = "actions/bin/filter@46ffca7632504e61db2d4cb16be1e80f333cb859"
  args = "branch master"
}

action "Login" {
  uses = "atlassian/gajira-login@v1.0.0"
  needs = ["Filters for GitHub Actions"]
  secrets = ["JIRA_API_TOKEN", "JIRA_BASE_URL", "JIRA_USER_EMAIL"]
}

action "Find in commit messages" {
  uses = "atlassian/gajira-find-issue-key@v1.0.0"
  needs = ["Login"]
  args = "--from=commits"
}

action "Transition to done" {
  uses = "atlassian/gajira-transition@v1.0.0"
  needs = ["Find in commit messages"]
  args = "Done"
}

workflow "Create issue" {
  on = "issues"
  resolves = ["Create Jira Issue"]
}

action "Filters opened" {
  uses = "actions/bin/filter@24a566c2524e05ebedadef0a285f72dc9b631411"
  args = "action opened"
}

action "Login " {
  uses = "atlassian/gajira-login@v1.0.0"
  needs = ["Filters opened"]
  secrets = ["JIRA_API_TOKEN", "JIRA_BASE_URL", "JIRA_USER_EMAIL"]
}

action "Create Jira Issue" {
  uses = "atlassian/gajira-create@v1.0.0"
  needs = ["Login "]
  args = "--project=GA --issuetype=Story --summary=\"{{ event.issue.title }}\" --description=$'{{ event.issue.body }}\\n\\n_Created from GitHub Action_'"
}

workflow "Create from TODO" {
  on = "push"
  resolves = ["TODO Create"]
}

action "Filter master" {
  uses = "actions/bin/filter@46ffca7632504e61db2d4cb16be1e80f333cb859"
  args = "branch master"
}

action "Login to Jira" {
  uses = "atlassian/gajira-login@v1.0.0"
  needs = ["Filter master"]
  secrets = ["JIRA_API_TOKEN", "JIRA_BASE_URL", "JIRA_USER_EMAIL"]
}

action "TODO Create" {
  uses = "atlassian/gajira-todo@v1.0.0"
  needs = ["Login to Jira"]
  secrets = ["GITHUB_TOKEN"]
  args = "--project=GA --issuetype=Task"
}
