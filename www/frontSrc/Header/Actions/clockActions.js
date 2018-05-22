export const REQUEST_CLOCK = 'REQUEST_CLOCK'
export const REQUEST_CLOCK_SUCCESS = 'REQUEST_CLOCK_SUCCESS'
export const REQUEST_CLOCK_FAIL = 'REQUEST_CLOCK_FAIL'

export function requestClock () {
  return {
    type: REQUEST_CLOCK,
  }
}

export function requestClockSuccess (res) {
  console.log('clock :', res.data)
  return {
    type: REQUEST_CLOCK_SUCCESS,
    data: res.data,
  }
}

export function requestClockFail (err) {
  return {
    type: REQUEST_CLOCK_FAIL,
    error: err,
  }
}