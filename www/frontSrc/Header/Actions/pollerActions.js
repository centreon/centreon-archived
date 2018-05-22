export const REQUEST_POLLERS = 'REQUEST_POLLERS'
export const REQUEST_POLLERS_SUCCESS = 'REQUEST_POLLERS_SUCCESS'
export const REQUEST_POLLERS_FAIL = 'REQUEST_POLLERS_FAIL'

export function requestPollers () {
  return {
    type: REQUEST_POLLERS,
  }
}

export function requestPollersSuccess (res) {
  return {
    type: REQUEST_POLLERS_SUCCESS,
    data: res.data,
  }
}

export function requestPollersFail (err) {
  return {
    type: REQUEST_POLLERS_FAIL,
    error: err.response,
  }
}