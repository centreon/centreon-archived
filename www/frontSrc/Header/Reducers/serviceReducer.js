import {
  REQUEST_USER,
  REQUEST_USER_SUCCESS,
  REQUEST_USER_FAIL,
} from '../Actions/userActions'

export default function userReducer (
  state = {
    data: {}
  },
  action
) {
  switch (action.type) {
    case REQUEST_USER:
      return {
        ...state
      }
    case REQUEST_USER_SUCCESS:
      return {
        ...state,
        data: action.data
      }
    default:
      return state
  }
}