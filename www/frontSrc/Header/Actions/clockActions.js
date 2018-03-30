export const REQUEST_CLOCK = 'REQUEST_CLOCK'
export const REQUEST_CLOCK_SUCCESS = 'REQUEST_CLOCK_SUCCESS'
export const REQUEST_CLOCK_FAIL = 'REQUEST_CLOCK_FAIL'
export const TIMER_START = 'TIMER_START'

export function requestClock () {
  return {
    type: REQUEST_CLOCK,
  }
}

export function requestClockSuccess (res) {

  return {
    type: REQUEST_CLOCK_SUCCESS,
    data: res.data,
  }
}

export function requestClockFail (err) {
  return {
    type: REQUEST_CLOCK_FAIL,
    error: err.response,
  }
}

function timerStarter (time) {
  return {
    type: TIMER_START,
    time: time + 1
  }
}

export function timeDispatcher (time) {
  return (dispatch) => {
    dispatch(timerStarter(time))
  }
}