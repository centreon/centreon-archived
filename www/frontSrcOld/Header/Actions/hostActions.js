export const REQUEST_HOSTS = 'REQUEST_HOSTS'
export const REQUEST_HOSTS_SUCCESS = 'REQUEST_HOSTS_SUCCESS'
export const REQUEST_HOSTS_FAIL = 'REQUEST_HOSTS_FAIL'

export function requestHosts () {
  return {
    type: REQUEST_HOSTS,
  }
}

export function requestHostsSuccess (res) {
  return {
    type: REQUEST_HOSTS_SUCCESS,
    data: res.data,
  }
}

export function requestHostsFail (err) {
  return {
    type: REQUEST_HOSTS_FAIL,
    error: err.response,
  }
}