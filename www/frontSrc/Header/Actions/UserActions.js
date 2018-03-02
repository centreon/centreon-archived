export const REQUEST_USER = 'REQUEST_USER'
export const REQUEST_USER_SUCCESS = 'REQUEST_USER_SUCCESS'
export const REQUEST_USER_FAIL = 'REQUEST_USER_FAIL'

export function requestUser () {
  return {
    type: REQUEST_USER,
  }
}

export function requestUserSuccess (res) {
  return {
    type: REQUEST_USER_SUCCESS,
    data: res,
  }
}

export function requestUserFail (err) {
  return {
    type: REQUEST_USER_FAIL,
    error: err,
  }
}