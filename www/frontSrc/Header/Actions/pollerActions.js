export const REQUEST_POLLERS_STATUS = 'REQUEST_POLLERS_STATUS'
export const REQUEST_POLLERS_STATUS_SUCCESS = 'REQUEST_POLLERS_STATUS_SUCCESS'
export const REQUEST_POLLERS_STATUS_FAIL = 'REQUEST_POLLERS_STATUS_FAIL'

export const REQUEST_POLLERS_LIST = 'REQUEST_POLLERS_LIST'
export const REQUEST_POLLERS_LIST_SUCCESS = 'REQUEST_POLLERS_LIST_SUCCESS'
export const REQUEST_POLLERS_LIST_FAIL = 'REQUEST_POLLERS_LIST_FAIL'

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

export function requestPollersList () {
  return {
    type: REQUEST_POLLERS_LIST,
  }
}

export function requestPollersListSuccess (res) {
  return {
    type: REQUEST_POLLERS_LIST_SUCCESS,
    data: res.data,
  }
}

export function requestPollersListFail (err) {
  return {
    type: REQUEST_POLLERS_LIST_FAIL,
    error: err.response,
  }
}