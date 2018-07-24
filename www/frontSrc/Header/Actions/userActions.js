export const REQUEST_USER = 'REQUEST_USER'
export const REQUEST_USER_SUCCESS = 'REQUEST_USER_SUCCESS'
export const REQUEST_USER_FAIL = 'REQUEST_USER_FAIL'

export const REQUEST_ENABLED_NOTIF = 'REQUEST_ENABLED_NOTIF'
export const REQUEST_ENABLED_NOTIF_SUCCESS = 'REQUEST_ENABLED_NOTIF_SUCCESS'
export const REQUEST_ENABLED_NOTIF_FAIL = 'REQUEST_ENABLED_NOTIF_FAIL'

export const REQUEST_DISABLED_NOTIF = 'REQUEST_DISABLED_NOTIF'
export const REQUEST_DISABLED_NOTIF_SUCCESS = 'REQUEST_DISABLED_NOTIF_SUCCESS'
export const REQUEST_DISABLED_NOTIF_FAIL = 'REQUEST_DISABLED_NOTIF_FAIL'


export const REQUEST_AUTOLOGIN = 'REQUEST_AUTOLOGIN'
export const REQUEST_AUTOLOGIN_SUCCESS = 'REQUEST_AUTOLOGIN_SUCCESS'
export const REQUEST_AUTOLOGIN_FAIL = 'REQUEST_AUTOLOGIN_FAIL'

export function requestUser () {
  return {
    type: REQUEST_USER,
  }
}

export function requestUserSuccess (res) {
  return {
    type: REQUEST_USER_SUCCESS,
    data: res.data,
  }
}

export function requestUserFail (err) {
  return {
    type: REQUEST_USER_FAIL,
    error: err.response,
  }
}

/********** Sound Notification ************/

export function requestEnabledNotif () {
  return {
    type: REQUEST_ENABLED_NOTIF,
  }
}

export function requestEnabledNotifSuccess (res) {
  return {
    type: REQUEST_ENABLED_NOTIF_SUCCESS,
    data: res.response,
  }
}

export function requestEnabledNotifFail (err) {
  return {
    type: REQUEST_ENABLED_NOTIF_FAIL,
    error: err.response,
  }
}

export function requestDisabledNotif () {
  return {
    type: REQUEST_DISABLED_NOTIF,
  }
}

export function requestDisabledNotifSuccess (res) {
  return {
    type: REQUEST_DISABLED_NOTIF_SUCCESS,
    data: res,
  }
}

export function requestDisabledNotifFail (err) {
  return {
    type: REQUEST_DISABLED_NOTIF_FAIL,
    error: err.response,
  }
}

/********** Autologin ************/

export function requestAutologin () {
  return {
    type: REQUEST_AUTOLOGIN,
  }
}

export function requestAutologinSuccess (res) {
  return {
    type: REQUEST_AUTOLOGIN_SUCCESS,
    data: res,
  }
}

export function requestAutologinFail (err) {
  return {
    type: REQUEST_AUTOLOGIN_FAIL,
    error: err.response,
  }
}