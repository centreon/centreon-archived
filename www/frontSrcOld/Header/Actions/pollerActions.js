export const REQUEST_POLLERS_STATUS = 'REQUEST_POLLERS_STATUS'
export const REQUEST_POLLERS_STATUS_SUCCESS = 'REQUEST_POLLERS_STATUS_SUCCESS'
export const REQUEST_POLLERS_STATUS_FAIL = 'REQUEST_POLLERS_STATUS_FAIL'

export const REQUEST_POLLERS_LIST_ISSUES = 'REQUEST_POLLERS_LIST_ISSUES'
export const REQUEST_POLLERS_LIST_ISSUES_SUCCESS = 'REQUEST_POLLERS_LIST_ISSUES_SUCCESS'
export const REQUEST_POLLERS_LIST_ISSUES_FAIL = 'REQUEST_POLLERS_LIST_ISSUES_FAIL'

export function requestPollersStatus () {
  return {
    type: REQUEST_POLLERS_STATUS,
  }
}

export function requestPollersStatusSuccess (res) {
  return {
    type: REQUEST_POLLERS_STATUS_SUCCESS,
    data: res.data,
  }
}

export function requestPollersStatusFail (err) {
  return {
    type: REQUEST_POLLERS_STATUS_FAIL,
    error: err.response,
  }
}

export function requestPollersListIssues () {
  return {
    type: REQUEST_POLLERS_LIST_ISSUES,
  }
}

export function requestPollersListIssuesSuccess (res) {
  return {
    type: REQUEST_POLLERS_LIST_ISSUES_SUCCESS,
    data: res.data,
  }
}

export function requestPollersListIssuesFail (err) {
  return {
    type: REQUEST_POLLERS_LIST_ISSUES_FAIL,
    error: err.response,
  }
}