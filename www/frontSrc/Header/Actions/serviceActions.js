export const REQUEST_SERVICES = 'REQUEST_SERVICES'
export const REQUEST_SERVICES_SUCCESS = 'REQUEST_SERVICES_SUCCESS'
export const REQUEST_SERVICES_FAIL = 'REQUEST_SERVICES_FAIL'

export function requestServices () {
  return {
    type: REQUEST_SERVICES,
  }
}

export function requestServicesSuccess (res) {
  return {
    type: REQUEST_SERVICES_SUCCESS,
    data: res.data,
  }
}

export function requestServicesFail (err) {
  return {
    type: REQUEST_SERVICES_FAIL,
    error: err.response,
  }
}