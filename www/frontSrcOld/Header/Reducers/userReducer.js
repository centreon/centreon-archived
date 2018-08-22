import {
  REQUEST_USER,
  REQUEST_USER_SUCCESS,
  REQUEST_USER_FAIL,
  REQUEST_ENABLED_NOTIF,
  REQUEST_ENABLED_NOTIF_SUCCESS,
  REQUEST_ENABLED_NOTIF_FAIL,
  REQUEST_DISABLED_NOTIF,
  REQUEST_DISABLED_NOTIF_SUCCESS,
  REQUEST_DISABLED_NOTIF_FAIL,
  REQUEST_AUTOLOGIN,
  REQUEST_AUTOLOGIN_SUCCESS,
  REQUEST_AUTOLOGIN_FAIL,
} from '../Actions/userActions'

export default function userReducer (
  state = {
    data: {},
    dataFetched: false,
    isFetching: false,
  },
  action
) {

  switch (action.type) {
    case REQUEST_USER:
      return {
        ...state,
        dataFetched: false,
        isFetching: true,
      }
    case REQUEST_USER_SUCCESS:
      return {
        ...state,
        data: action.data,
        dataFetched: true,
        isFetching: false,
      }
    case REQUEST_USER_FAIL:
      return {
        ...state,
        err: action.error,
        isFetching: false,
        dataFetched: false,
      }
    case REQUEST_ENABLED_NOTIF:
      return {
        ...state
      }
    case REQUEST_ENABLED_NOTIF_SUCCESS:
      return {
        ...state,
        message: 'Enable sound notification !'
      }
    case REQUEST_ENABLED_NOTIF_FAIL:
      return {
        ...state,
        err: action.error
      }
    case REQUEST_DISABLED_NOTIF:
      return {
        ...state
      }
    case REQUEST_DISABLED_NOTIF_SUCCESS:
      return {
        ...state,
        message: 'Disable sound notification !'
      }
    case REQUEST_DISABLED_NOTIF_FAIL:
      return {
        ...state,
        err: action.error
      }
    case REQUEST_AUTOLOGIN:
      return {
        ...state
      }
    case REQUEST_AUTOLOGIN_SUCCESS:
      return {
        ...state,
        message: 'Activate autologin'
      }
    case REQUEST_AUTOLOGIN_FAIL:
      return {
        ...state,
        err: action.error
      }
    default:
      return state
  }
}